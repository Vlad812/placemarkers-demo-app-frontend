<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class SessionTokenExtractor implements TokenExtractorInterface
{
    public function __construct(
        private AuthSessionStorageInterface $authSessionStorage,
    ) {
    }

    public function extract(Request $request): false|string
    {
        if (!$request->hasSession()) {
            return false;
        }

        $token = $this->authSessionStorage->getAccessToken();

        return $token ?? false;
    }
}
