<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class AuthSessionStorage
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function store(string $accessToken, string $refreshToken): void
    {
        $session = $this->getSession();
        $session->set(AuthSessionKeys::ACCESS_TOKEN, $accessToken);
        $session->set(AuthSessionKeys::REFRESH_TOKEN, $refreshToken);
    }

    public function getAccessToken(): ?string
    {
        $token = $this->getSession()->get(AuthSessionKeys::ACCESS_TOKEN);

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function getRefreshToken(): ?string
    {
        $token = $this->getSession()->get(AuthSessionKeys::REFRESH_TOKEN);

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function clear(): void
    {
        $session = $this->getSession();
        $session->remove(AuthSessionKeys::ACCESS_TOKEN);
        $session->remove(AuthSessionKeys::REFRESH_TOKEN);
    }

    public function invalidate(): void
    {
        $this->getSession()->invalidate();
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
