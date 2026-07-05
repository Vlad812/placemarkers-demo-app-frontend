<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder;

use App\Application\DTO\ErrorResponse;
use App\Application\DTO\ResponsePayloadInterface;
use App\Application\DTO\StringPayload;
use Symfony\Component\HttpFoundation\Response;

final readonly class PlainTextResponder implements ResponderInterface
{
    public function respond(ResponsePayloadInterface $payload): Response
    {
        if (!$payload instanceof StringPayload) {
            throw new \InvalidArgumentException(sprintf('Expected StringPayload, got [%s].', $payload::class));
        }

        return new Response($payload->content, $payload->getStatusCode());
    }

    public function respondError(ErrorResponse $error): Response
    {
        $message = $error->message;

        if ($error->incidentId !== null) {
            $message .= sprintf(' Incident ID: %s', $error->incidentId);
        }

        return new Response($message, $error->statusCode);
    }
}
