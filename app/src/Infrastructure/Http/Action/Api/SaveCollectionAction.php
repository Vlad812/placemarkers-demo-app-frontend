<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Command\Api\SaveCollectionCommand;
use App\Application\Handler\Api\SaveCollectionHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/collections',
    name: 'api_placemarker_collection_save',
    methods: ['POST'],
)]
final class SaveCollectionAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly SaveCollectionHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = SaveCollectionCommand::fromRawValues($request->toArray());
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
