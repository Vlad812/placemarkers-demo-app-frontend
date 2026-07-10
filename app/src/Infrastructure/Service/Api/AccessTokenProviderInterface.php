<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api;

interface AccessTokenProviderInterface
{
    /**
     * @return array<string, string>
     */
    public function getAuthorizationHeaders(): array;
}
