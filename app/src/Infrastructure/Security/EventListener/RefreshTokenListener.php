<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\EventListener;

use App\Application\DTO\Api\Payload\AuthRefreshPayload;
use App\Application\Exception\ApiException;
use App\Application\Exception\ServiceUnavailableException;
use App\Application\Exception\UnauthorizedException;
use App\Application\Port\Api\AuthApiInterface;
use App\Infrastructure\Security\AuthSessionStorageInterface;
use App\Infrastructure\Service\IncidentLoggerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class RefreshTokenListener
{
    public function __construct(
        private AuthApiInterface            $apiClient,
        private AuthSessionStorageInterface $authSessionStorage,
        private JWTEncoderInterface         $jwtEncoder,
        private IncidentLoggerInterface     $incidentLogger,
    ) {
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $refreshToken = $this->authSessionStorage->getRefreshToken();

        if ($refreshToken === null) {
            return;
        }

        $accessToken = $this->authSessionStorage->getAccessToken();
        $needsRefresh = false;

        if ($accessToken === null) {
            $needsRefresh = true;
        } else {
            try {
                $this->jwtEncoder->decode($accessToken);
            } catch (JWTDecodeFailureException $e) {
                if ($e->getReason() === JWTDecodeFailureException::EXPIRED_TOKEN) {
                    $needsRefresh = true;
                }
            }
        }

        if (!$needsRefresh) {
            return;
        }

        try {
            $result = $this->apiClient->refresh(new AuthRefreshPayload($refreshToken));

            $this->authSessionStorage->store(
                $result->accessToken,
                $result->refreshToken !== '' ? $result->refreshToken : $refreshToken,
            );
        } catch (ServiceUnavailableException) {
            $this->incidentLogger->logErrorMessage(
                'Auth service unavailable during token refresh.',
            );
        } catch (ApiException|UnauthorizedException) {
            $this->authSessionStorage->invalidate();
        }
    }
}
