<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\DTO\HtmlPageResponse;
use App\Application\Query\Auth\LoginPageQuery;

final readonly class LoginPageHandler
{
    public function __invoke(LoginPageQuery $query): HtmlPageResponse
    {
        return new HtmlPageResponse(['error' => null, 'email' => '']);
    }
}
