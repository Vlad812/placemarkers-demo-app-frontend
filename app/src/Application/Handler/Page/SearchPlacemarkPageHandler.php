<?php

declare(strict_types=1);

namespace App\Application\Handler\Page;

use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\RedirectPageResponse;
use App\Application\Query\Page\SearchPlacemarkPageQuery;
use App\Application\Service\AuthenticatedUserProvider;
use App\Application\Port\Api\SearchApiInterface;

final readonly class SearchPlacemarkPageHandler
{
    public function __construct(
        private AuthenticatedUserProvider $userProvider,
        private SearchApiInterface $searchApiClient,
    ) {
    }

    public function __invoke(SearchPlacemarkPageQuery $query): HtmlPageResponse|RedirectPageResponse
    {
        if (!$this->userProvider->isAuthenticated()) {
            return new RedirectPageResponse('auth_login_page');
        }

        $userTags = $this->searchApiClient->getUserTags()->body;
        $placemarkerTypes = $this->searchApiClient->getPlacemarkerTypes()->body;

        return new HtmlPageResponse([
            'userTags' => $userTags,
            'placemarkerTypes' => $placemarkerTypes,
        ]);
    }
}
