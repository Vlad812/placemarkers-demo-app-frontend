<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder\Auth;

use App\Infrastructure\Http\Responder\AbstractPageResponder;

final readonly class SignupResponder extends AbstractPageResponder
{
    protected function getTemplate(): string
    {
        return 'auth/signup.html.twig';
    }
}
