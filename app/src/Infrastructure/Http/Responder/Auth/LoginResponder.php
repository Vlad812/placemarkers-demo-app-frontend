<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Auth;

use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\ResponsePayloadInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class LoginResponder extends AbstractPageResponder
{
    protected function getTemplate(): string
    {
        return 'auth/login.html.twig';
    }
}
