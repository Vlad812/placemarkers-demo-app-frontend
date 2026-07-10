<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Handler\Page\CreatePlacemarkPageHandler;
use App\Application\Query\Page\CreatePlacemarkPageQuery;
use App\Infrastructure\Http\Responder\Page\CreatePlacemarkPageResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/create',
    name: 'placemark_create',
    methods: ['GET'],
)]
final class CreatePlacemarkPageAction extends AbstractAction
{
    public function __construct(
        CreatePlacemarkPageResponder $responder,
        private readonly CreatePlacemarkPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new CreatePlacemarkPageQuery());

        return $this->responder->respond($result);
    }
}
