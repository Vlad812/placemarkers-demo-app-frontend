<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Auth;

use App\Application\DTO\ErrorResponse;
use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\ResponsePayloadInterface;
use App\Infrastructure\Http\Responder\AbstractPageResponder;
use App\Infrastructure\Http\Responder\RedirectResponder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final readonly class ConfirmEmailResponder extends AbstractPageResponder
{
    public function __construct(
        Environment $twig,
        RedirectResponder $redirectResponder,
        private UrlGeneratorInterface $urlGenerator,
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

    public function respondError(ErrorResponse $error): Response
    {
        $context = array_merge(
            $error->context,
            [
                'error' => $error->message,
                'success' => false,
                'loginUrl' => $this->urlGenerator->generate('auth_login_page'),
            ],
        );

        if ($error->incidentId !== null) {
            $context['incident_id'] = $error->incidentId;
        }

        return $this->renderTemplate($context, $error->statusCode);
    }

    protected function getTemplate(): string
    {
        return 'auth/confirm_email.html.twig';
    }
}
