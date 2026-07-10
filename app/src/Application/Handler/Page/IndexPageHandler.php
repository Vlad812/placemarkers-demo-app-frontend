<?php

declare(strict_types=1);

namespace App\Application\Handler\Page;

use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\RedirectPageResponse;
use App\Application\Query\Page\IndexPageQuery;
use App\Application\Service\AuthenticatedUserProvider;

final readonly class IndexPageHandler
{
    public function __construct(
        private AuthenticatedUserProvider $userProvider,
    ) {
    }

    public function __invoke(IndexPageQuery $query): HtmlPageResponse|RedirectPageResponse
    {
        if (!$this->userProvider->isAuthenticated()) {
            return new RedirectPageResponse('auth_login_page');
        }

        return new HtmlPageResponse();
    }
}
