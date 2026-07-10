<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\DTO\HtmlPageResponse;
use App\Application\Query\Auth\ForgotPasswordPageQuery;

final readonly class ForgotPasswordPageHandler
{
    public function __invoke(ForgotPasswordPageQuery $query): HtmlPageResponse
    {
        return new HtmlPageResponse([
            'error' => null,
            'success' => null,
            'email' => '',
        ]);
    }
}
