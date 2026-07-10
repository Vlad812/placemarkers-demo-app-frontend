<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Command\Auth\LoginCommand;
use App\Application\DTO\Api\Payload\AuthLoginPayload;
use App\Application\DTO\LoginSuccessResponse;
use App\Application\Port\Api\AuthApiInterface;

final readonly class LoginHandler
{
    public function __construct(
        private AuthApiInterface $apiClient,
    ) {
    }

    public function __invoke(LoginCommand $command): LoginSuccessResponse
    {
        $result = $this->apiClient->login(AuthLoginPayload::fromCommand($command));

        return new LoginSuccessResponse(
            route: 'placemark_create',
            accessToken: $result->accessToken,
            refreshToken: $result->refreshToken,
            expiresIn: $result->expiresIn,
            refreshExpiresIn: $result->refreshExpiresIn,
        );
    }
}
