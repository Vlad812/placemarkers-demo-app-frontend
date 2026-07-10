<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Handler\Page\TagsPageHandler;
use App\Application\Query\Page\TagsPageQuery;
use App\Infrastructure\Http\Responder\Page\TagsPageResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/tags',
    name: 'tags_index',
    methods: ['GET'],
)]
final class TagsPageAction extends AbstractAction
{
    public function __construct(
        TagsPageResponder $responder,
        private readonly TagsPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new TagsPageQuery());

        return $this->responder->respond($result);
    }
}
