<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\DTO\HtmlPageResponse;
use App\Application\Query\Auth\SignupPageQuery;

final readonly class SignupPageHandler
{
    public function __invoke(SignupPageQuery $query): HtmlPageResponse
    {
        return new HtmlPageResponse(['error' => null, 'success' => null, 'email' => '']);
    }
}
