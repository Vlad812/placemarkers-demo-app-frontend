<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Api;

use App\Application\Handler\Api\GetPlacemarkerHandler;
use App\Application\Query\Api\GetPlacemarkerQuery;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/search/placemarkers/{id}',
    name: 'api_search_get_placemarker',
    methods: ['GET'],
)]
final class GetPlacemarkerAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly GetPlacemarkerHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $query = GetPlacemarkerQuery::fromRawValues([
            'id' => (string) $request->attributes->get('id'),
        ]);
        $result = ($this->handler)($query);

        return $this->responder->respond($result);
    }
}
