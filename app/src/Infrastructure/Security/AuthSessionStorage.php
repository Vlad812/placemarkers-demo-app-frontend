<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Security\Event\SessionInvalidatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class AuthSessionStorage implements AuthSessionStorageInterface
{
    private const string ACCESS_TOKEN_KEY = 'auth.access_token';

    private const string REFRESH_TOKEN_KEY = 'auth.refresh_token';

    public function __construct(
        private RequestStack $requestStack,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function store(string $accessToken, string $refreshToken): void
    {
        $session = $this->getSession();
        $session->set(self::ACCESS_TOKEN_KEY, $accessToken);
        $session->set(self::REFRESH_TOKEN_KEY, $refreshToken);
    }

    public function getAccessToken(): ?string
    {
        $token = $this->getSession()->get(self::ACCESS_TOKEN_KEY);

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function getRefreshToken(): ?string
    {
        $token = $this->getSession()->get(self::REFRESH_TOKEN_KEY);

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function clear(): void
    {
        $session = $this->getSession();
        $session->remove(self::ACCESS_TOKEN_KEY);
        $session->remove(self::REFRESH_TOKEN_KEY);
    }

    public function invalidate(): void
    {
        $this->getSession()->invalidate();
        $this->eventDispatcher->dispatch(new SessionInvalidatedEvent());
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
