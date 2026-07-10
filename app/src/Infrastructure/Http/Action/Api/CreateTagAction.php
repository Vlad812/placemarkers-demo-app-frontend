<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Command\Api\CreateTagCommand;
use App\Application\Handler\Api\CreateTagHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/tags',
    name: 'api_tag_create',
    methods: ['POST'],
)]
final class CreateTagAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly CreateTagHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = CreateTagCommand::fromRawValues($request->toArray());
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
