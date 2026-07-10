<?php

declare(strict_types=1);

namespace App\Application\Handler\Page;

use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\RedirectPageResponse;
use App\Application\Query\Page\CreatePlacemarkPageQuery;
use App\Application\Service\AuthenticatedUserProvider;
use App\Application\Port\Api\PlacemarkerApiInterface;
use App\Application\Port\Api\SearchApiInterface;

final readonly class CreatePlacemarkPageHandler
{
    public function __construct(
        private AuthenticatedUserProvider $userProvider,
        private PlacemarkerApiInterface $placemarkerApiClient,
        private SearchApiInterface $searchApiClient,
    ) {
    }

    public function __invoke(CreatePlacemarkPageQuery $query): HtmlPageResponse|RedirectPageResponse
    {
        if (!$this->userProvider->isAuthenticated()) {
            return new RedirectPageResponse('auth_login_page');
        }

        $savedPlacemarkers = $this->placemarkerApiClient->getAll()->body;
        $userTags = $this->searchApiClient->getUserTags()->body;
        $placemarkerTypes = $this->searchApiClient->getPlacemarkerTypes()->body;

        return new HtmlPageResponse([
            'savedPlacemarkers' => $savedPlacemarkers,
            'userTags' => $userTags,
            'placemarkerTypes' => $placemarkerTypes,
        ]);
    }
}
