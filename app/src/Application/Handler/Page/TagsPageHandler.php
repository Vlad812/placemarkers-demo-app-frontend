<?php

declare(strict_types=1);

namespace App\Application\Handler\Page;

use App\Application\DTO\HtmlPageResponse;
use App\Application\DTO\RedirectPageResponse;
use App\Application\Query\Page\TagsPageQuery;
use App\Application\Service\AuthenticatedUserProvider;
use App\Application\Port\Api\SearchApiInterface;

final readonly class TagsPageHandler
{
    public function __construct(
        private AuthenticatedUserProvider $userProvider,
        private SearchApiInterface $searchApiClient,
    ) {
    }

    public function __invoke(TagsPageQuery $query): HtmlPageResponse|RedirectPageResponse
    {
        if (!$this->userProvider->isAuthenticated()) {
            return new RedirectPageResponse('auth_login_page');
        }

        $userTags = $this->searchApiClient->getUserTags()->body;
        $placemarkerTypes = $this->searchApiClient->getPlacemarkerTypes()->body;

        $tagsByType = [];
        foreach (is_array($userTags) ? $userTags : [] as $tag) {
            if (!is_array($tag)) {
                continue;
            }
            $typeId = is_string($tag['type_id'] ?? null) ? $tag['type_id'] : 'default';
            $tagsByType[$typeId][] = $tag;
        }

        return new HtmlPageResponse([
            'userTags' => $userTags,
            'placemarkerTypes' => $placemarkerTypes,
            'tagsByType' => $tagsByType,
        ]);
    }
}
