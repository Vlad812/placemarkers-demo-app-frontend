<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Handler\Page\SearchPlacemarkPageHandler;
use App\Application\Query\Page\SearchPlacemarkPageQuery;
use App\Infrastructure\Http\Responder\Page\SearchPlacemarkPageResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/search',
    name: 'placemark_search',
    methods: ['GET'],
)]
final class SearchPlacemarkPageAction extends AbstractAction
{
    public function __construct(
        SearchPlacemarkPageResponder $responder,
        private readonly SearchPlacemarkPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new SearchPlacemarkPageQuery());

        return $this->responder->respond($result);
    }
}
