<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Command\Api\UpdatePlacemarkerCommand;
use App\Application\Handler\Api\UpdatePlacemarkerHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/placemarkers/{id}',
    name: 'api_placemarker_update',
    methods: ['PUT'],
)]
final class UpdatePlacemarkerAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly UpdatePlacemarkerHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = UpdatePlacemarkerCommand::fromRawValues([
            ...$request->toArray(),
            'id' => (string) $request->attributes->get('id'),
        ]);
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
