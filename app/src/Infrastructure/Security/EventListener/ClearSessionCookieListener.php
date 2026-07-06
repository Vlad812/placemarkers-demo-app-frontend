<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\EventListener;

use App\Infrastructure\Security\Event\SessionInvalidatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ClearSessionCookieListener
{
    private bool $shouldClearCookie = false;

    /**
     * @param array<string, mixed> $sessionStorageOptions
     */
    public function __construct(
        private readonly array $sessionStorageOptions,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[AsEventListener(event: SessionInvalidatedEvent::class)]
    public function onSessionInvalidated(SessionInvalidatedEvent $event): void
    {
        $this->shouldClearCookie = true;
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->shouldClearCookie) {
            return;
        }

        $options = $this->sessionStorageOptions;

        $event->getResponse()->headers->clearCookie(
            (string) ($options['name'] ?? 'PHPSESSID'),
            (string) ($options['cookie_path'] ?? '/'),
            isset($options['cookie_domain']) ? (string) $options['cookie_domain'] : null,
            $this->resolveCookieSecure($options['cookie_secure'] ?? false),
            (bool) ($options['cookie_httponly'] ?? true),
            $this->normalizeSameSite($options['cookie_samesite'] ?? Cookie::SAMESITE_LAX),
        );

        $this->shouldClearCookie = false;
    }

    private function resolveCookieSecure(mixed $cookieSecure): bool
    {
        if ($cookieSecure === 'auto') {
            return $this->requestStack->getCurrentRequest()?->isSecure() ?? false;
        }

        return (bool) $cookieSecure;
    }

    private function normalizeSameSite(mixed $sameSite): ?string
    {
        if ($sameSite === null || $sameSite === false) {
            return null;
        }

        return (string) $sameSite;
    }
}
