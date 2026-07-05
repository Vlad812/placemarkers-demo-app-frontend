<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Auth;

use App\Application\DTO\ErrorResponse;
use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\RedirectPageResponse;
use App\Application\DTO\ResponsePayloadInterface;
use App\Infrastructure\Http\Responder\RedirectResponder;
use App\Infrastructure\Http\Responder\ResponderInterface;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

abstract readonly class AbstractPageResponder implements ResponderInterface
{
    public function __construct(
        protected Environment       $twig,
        protected RedirectResponder $redirectResponder,
    ) {
    }

    abstract protected function getTemplate(): string;

    public function respond(ResponsePayloadInterface $payload): Response
    {
        if ($payload instanceof RedirectPageResponse) {
            return $this->redirectResponder->respond($payload);
        }

        if ($payload instanceof HtmlPageResponse) {
            return $this->renderTemplate($payload->context, $payload->statusCode, $payload->headers);
        }

        throw new LogicException(sprintf('Unsupported payload [%s] for page responder.', $payload::class));
    }

    public function respondError(ErrorResponse $error): Response
    {
        $context = array_merge(
            $error->context,
            ['error' => $error->message],
        );

        if ($error->incidentId !== null) {
            $context['incident_id'] = $error->incidentId;
        }

        return $this->renderTemplate($context, $error->statusCode);
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, string> $headers
     */
    protected function renderTemplate(array $context, int $statusCode = Response::HTTP_OK, array $headers = []): Response
    {
        return new Response(
            $this->twig->render($this->getTemplate(), $context),
            $statusCode,
            $headers,
        );
    }
}
