<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder;

use App\Application\DTO\CookieData;
use App\Application\DTO\ErrorResponse;
use App\Application\DTO\RedirectPageResponse;
use App\Application\DTO\ResponsePayloadInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class RedirectResponder implements ResponderInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function respond(ResponsePayloadInterface $payload): RedirectResponse
    {
        if (!$payload instanceof RedirectPageResponse) {
            throw new \InvalidArgumentException(sprintf('Expected RedirectPageResponse, got [%s].', $payload::class));
        }

        $response = new RedirectResponse(
            $this->urlGenerator->generate($payload->route, $payload->routeParams),
            $payload->statusCode,
        );

        foreach ($payload->cookies as $cookie) {
            $response->headers->setCookie($this->buildCookie($cookie));
        }

        foreach ($payload->clearCookies as $cookieName) {
            $response->headers->clearCookie($cookieName);
        }

        return $response;
    }

    public function respondError(ErrorResponse $error): Response
    {
        return new RedirectResponse('/', Response::HTTP_FOUND);
    }

    private function buildCookie(CookieData $data): Cookie
    {
        return Cookie::create(
            $data->name,
            $data->value,
            $data->expires,
            $data->path,
            $data->domain,
            $data->secure,
            $data->httpOnly,
            $data->raw,
            $data->sameSite ?? Cookie::SAMESITE_LAX,
        );
    }
}
