<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Command\Auth\SignupCommand;
use App\Application\DTO\Api\Payload\AuthSignupPayload;
use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\AuthApiInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class SignupHandler
{
    public function __construct(
        private AuthApiInterface $apiClient,
    ) {
    }

    public function __invoke(SignupCommand $command): ApiProxyResponse
    {
        $signupResult = $this->apiClient->signup(AuthSignupPayload::fromCommand($command));

        return new ApiProxyResponse(
            Response::HTTP_CREATED,
            [
                'success' => $signupResult->message !== ''
                    ? $signupResult->message
                    : 'Регистрация прошла успешно. Проверьте почту и перейдите по ссылке для подтверждения email.',
            ],
        );
    }
}
