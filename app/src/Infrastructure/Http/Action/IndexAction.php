<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Handler\Page\IndexPageHandler;
use App\Application\Query\Page\IndexPageQuery;
use App\Infrastructure\Http\Responder\Page\IndexPageResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/',
    name: 'home',
    methods: ['GET'],
)]
final class IndexAction extends AbstractAction
{
    public function __construct(
        IndexPageResponder $responder,
        private readonly IndexPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new IndexPageQuery());

        return $this->responder->respond($result);
    }
}
