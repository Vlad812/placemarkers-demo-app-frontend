<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder;

use App\Application\DTO\ErrorResponse;
use App\Application\DTO\ResponsePayloadInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResponderInterface
{
    public function respond(ResponsePayloadInterface $payload): Response;

    public function respondError(ErrorResponse $error, ?Request $request = null): Response;
}
