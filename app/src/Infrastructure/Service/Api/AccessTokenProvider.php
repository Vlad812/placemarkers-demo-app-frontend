<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api;

use App\Infrastructure\Security\AuthSessionStorageInterface;

final readonly class AccessTokenProvider implements AccessTokenProviderInterface
{
    public function __construct(
        private AuthSessionStorageInterface $authSessionStorage,
    ) {
    }

    /**
     * @return array
     */
    public function getAuthorizationHeaders(): array
    {
        $token = $this->authSessionStorage->getAccessToken();

        if ($token === null) {
            return [];
        }

        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
