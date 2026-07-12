<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\DTO\ApiProxyResponse;
use App\Application\Query\Api\GetPlacemarkerQuery;
use App\Application\Port\Api\SearchApiInterface;

final readonly class GetPlacemarkerHandler
{
    public function __construct(
        private SearchApiInterface $apiClient,
    ) {
    }

    public function __invoke(GetPlacemarkerQuery $query): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->getPlacemarker($query->id),
        );
    }
}
