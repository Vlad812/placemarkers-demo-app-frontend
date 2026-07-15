<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\DTO\ApiProxyResponse;
use App\Application\Query\Api\SearchPlacemarkersQuery;
use App\Application\Port\Api\SearchApiInterface;

final readonly class SearchPlacemarkersHandler
{
    public function __construct(
        private SearchApiInterface $apiClient,
    ) {
    }

    public function __invoke(SearchPlacemarkersQuery $query): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->search(
                $query->lat,
                $query->lon,
                $query->radius,
                $query->filters,
            ),
        );
    }
}
