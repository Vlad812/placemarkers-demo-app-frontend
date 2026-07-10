<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Command\Api\DeleteCollectionCommand;
use App\Application\Handler\Api\DeleteCollectionHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/collections/{id}',
    name: 'api_placemarker_collection_delete',
    methods: ['DELETE'],
)]
final class DeleteCollectionAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly DeleteCollectionHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = DeleteCollectionCommand::fromRawValues([
            'id' => (string) $request->attributes->get('id'),
        ]);
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
