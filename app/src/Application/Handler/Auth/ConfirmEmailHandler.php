<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Command\Auth\ConfirmEmailCommand;
use App\Application\DTO\HtmlPageResponse;
use App\Application\Port\Api\AuthApiInterface;

final readonly class ConfirmEmailHandler
{
    public function __construct(
        private AuthApiInterface $apiClient,
    ) {
    }

    public function __invoke(ConfirmEmailCommand $command): HtmlPageResponse
    {
        $this->apiClient->confirmEmail($command->token);

        return new HtmlPageResponse([
            'success' => true,
            'error' => null,
        ]);
    }
}
