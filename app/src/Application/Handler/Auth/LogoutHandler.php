<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Command\Auth\LogoutCommand;
use App\Application\DTO\RedirectPageResponse;
use App\Application\Port\Api\AuthApiInterface;
use App\Infrastructure\Security\AuthSessionStorageInterface;
use App\Infrastructure\Service\IncidentLoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class LogoutHandler
{
    public function __construct(
        private AuthApiInterface $apiClient,
        private AuthSessionStorageInterface $authSessionStorage,
        private TokenStorageInterface $tokenStorage,
        private IncidentLoggerInterface $incidentLogger,
    ) {
    }

    public function __invoke(LogoutCommand $command): RedirectPageResponse
    {
        $accessToken = $this->authSessionStorage->getAccessToken();
        $refreshToken = $this->authSessionStorage->getRefreshToken();

        if ($accessToken !== null) {
            try {
                $this->apiClient->logout($accessToken, $refreshToken);
            } catch (\Throwable $e) {
                $this->incidentLogger->logError('Failed to revoke tokens on auth service during logout.', $e);
            }
        }

        $this->tokenStorage->setToken(null);
        $this->authSessionStorage->invalidate();

        return new RedirectPageResponse('auth_login_page');
    }
}
