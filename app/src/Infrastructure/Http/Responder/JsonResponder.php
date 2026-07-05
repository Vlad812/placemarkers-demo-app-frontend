<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder;

use App\Application\DTO\ErrorResponse;
use App\Application\DTO\ResponsePayloadInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class JsonResponder implements ResponderInterface
{
    /**
     * @param array<string, string> $presetHeaders
     */
    public function __construct(
        private array $presetHeaders = [],
    ) {
    }

    public function respond(ResponsePayloadInterface $payload): JsonResponse
    {
        return new JsonResponse(
            $payload->getBody(),
            $payload->getStatusCode(),
            $this->presetHeaders,
        );
    }

    public function respondError(ErrorResponse $error, ?Request $request = null): JsonResponse
    {
        $body = array_merge($error->context, ['message' => $error->message]);

        if ($error->incidentId !== null) {
            $body['incident_id'] = $error->incidentId;
        }

        return new JsonResponse(
            $body,
            $error->statusCode,
            $this->presetHeaders,
        );
    }
}
