<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\DTO\HtmlPageResponse;
use App\Application\Query\Auth\ResetPasswordPageQuery;

final readonly class ResetPasswordPageHandler
{
    public function __invoke(ResetPasswordPageQuery $query): HtmlPageResponse
    {
        return new HtmlPageResponse([
            'token' => $query->token,
            'error' => null,
            'success' => null,
        ]);
    }
}
