<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Command\Auth\ForgotPasswordCommand;
use App\Application\DTO\Api\Payload\AuthRequestPasswordResetPayload;
use App\Application\DTO\HtmlPageResponse;
use App\Application\Port\Api\AuthApiInterface;

final readonly class ForgotPasswordHandler
{
    public function __construct(
        private AuthApiInterface $apiClient,
    ) {
    }

    public function __invoke(ForgotPasswordCommand $command): HtmlPageResponse
    {
        $result = $this->apiClient->requestPasswordReset(AuthRequestPasswordResetPayload::fromCommand($command));

        return new HtmlPageResponse([
            'error' => null,
            'success' => $result->message !== ''
                ? $result->message
                : 'Если аккаунт с таким email существует, инструкции отправлены на почту.',
            'email' => '',
        ]);
    }
}
