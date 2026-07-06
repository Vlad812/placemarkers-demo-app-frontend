<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Handler\HealthCheckHandler;
use App\Application\Query\HealthCheckQuery;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/health',
    name: 'health_check',
    methods: ['GET'],
)]
final class HealthCheckAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly HealthCheckHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new HealthCheckQuery());

        return $this->responder->respond($result);
    }
}
