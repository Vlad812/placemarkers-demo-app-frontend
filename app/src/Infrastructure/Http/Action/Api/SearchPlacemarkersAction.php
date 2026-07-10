<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Handler\Api\SearchPlacemarkersHandler;
use App\Application\Query\Api\SearchPlacemarkersQuery;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/search',
    name: 'api_placemarker_search',
    methods: ['GET'],
)]
final class SearchPlacemarkersAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly SearchPlacemarkersHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $query = SearchPlacemarkersQuery::fromRawValues($request->query->all());
        $result = ($this->handler)($query);

        return $this->responder->respond($result);
    }
}
