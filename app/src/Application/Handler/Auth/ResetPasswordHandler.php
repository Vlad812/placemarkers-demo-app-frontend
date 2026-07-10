<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Command\Auth\ResetPasswordCommand;
use App\Application\DTO\Api\Payload\AuthResetPasswordPayload;
use App\Application\DTO\HtmlPageResponse;
use App\Application\Port\Api\AuthApiInterface;

final readonly class ResetPasswordHandler
{
    public function __construct(
        private AuthApiInterface $apiClient,
    ) {
    }

    public function __invoke(ResetPasswordCommand $command): HtmlPageResponse
    {
        $result = $this->apiClient->resetPassword(AuthResetPasswordPayload::fromCommand($command));

        return new HtmlPageResponse([
            'token' => $command->token,
            'error' => null,
            'success' => $result->message !== '' ? $result->message : 'Пароль успешно изменён.',
        ]);
    }
}
