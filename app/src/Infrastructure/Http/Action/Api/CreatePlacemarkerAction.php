<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Command\Api\CreatePlacemarkerCommand;
use App\Application\Handler\Api\CreatePlacemarkerHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/placemarkers',
    name: 'api_placemarker_create',
    methods: ['POST'],
)]
final class CreatePlacemarkerAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly CreatePlacemarkerHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = CreatePlacemarkerCommand::fromRawValues($request->toArray());
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
