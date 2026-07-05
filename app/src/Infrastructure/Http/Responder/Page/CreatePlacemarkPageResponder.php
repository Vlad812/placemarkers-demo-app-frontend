<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Page;

use App\Infrastructure\Http\Responder\Auth\AbstractPageResponder;

final readonly class CreatePlacemarkPageResponder extends AbstractPageResponder
{
    protected function getTemplate(): string
    {
        return 'placemark/create.html.twig';
    }
}
