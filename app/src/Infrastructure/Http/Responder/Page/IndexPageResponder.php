<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Page;

use App\Infrastructure\Http\Responder\AbstractPageResponder;

final readonly class IndexPageResponder extends AbstractPageResponder
{
    protected function getTemplate(): string
    {
        return 'index.html.twig';
    }
}
