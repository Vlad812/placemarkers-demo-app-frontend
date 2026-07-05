<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Auth;

use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\ResponsePayloadInterface;
use App\Infrastructure\Http\Responder\AbstractPageResponder;
use App\Infrastructure\Http\Responder\RedirectResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final readonly class ResetPasswordResponder extends AbstractPageResponder
{
    public function __construct(
        Environment $twig,
        RedirectResponder $redirectResponder,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($twig, $redirectResponder);
    }

    public function respond(ResponsePayloadInterface $payload): Response
    {
        if ($payload instanceof HtmlPageResponse && !isset($payload->context['loginUrl'])) {
            $payload = new HtmlPageResponse(
                array_merge($payload->context, ['loginUrl' => $this->urlGenerator->generate('auth_login_page')]),
                $payload->statusCode,
                $payload->headers,
            );
        }

        return parent::respond($payload);
    }

    protected function getTemplate(): string
    {
        return 'auth/reset_password.html.twig';
    }
}
