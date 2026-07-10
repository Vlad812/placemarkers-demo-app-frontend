<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Command\Api\DeletePlacemarkerCommand;
use App\Application\Handler\Api\DeletePlacemarkerHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/placemarkers/{id}',
    name: 'api_placemarker_delete',
    methods: ['DELETE'],
)]
final class DeletePlacemarkerAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly DeletePlacemarkerHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = DeletePlacemarkerCommand::fromRawValues([
            'id' => (string) $request->attributes->get('id'),
        ]);
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
