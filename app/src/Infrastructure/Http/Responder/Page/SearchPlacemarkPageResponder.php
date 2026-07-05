<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Page;

use App\Infrastructure\Http\Responder\AbstractPageResponder;

final readonly class SearchPlacemarkPageResponder extends AbstractPageResponder
{
    protected function getTemplate(): string
    {
        return 'placemark/search.html.twig';
    }
}
