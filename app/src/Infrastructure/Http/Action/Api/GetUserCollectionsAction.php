<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Handler\Api\GetUserCollectionsHandler;
use App\Application\Query\Api\GetUserCollectionsQuery;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/collections',
    name: 'api_placemarker_collection_list',
    methods: ['GET'],
)]
final class GetUserCollectionsAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly GetUserCollectionsHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new GetUserCollectionsQuery());

        return $this->responder->respond($result);
    }
}
