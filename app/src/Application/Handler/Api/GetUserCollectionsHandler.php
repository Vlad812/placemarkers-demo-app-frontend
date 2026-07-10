<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\CollectionApiInterface;
use App\Application\Query\Api\GetUserCollectionsQuery;

final readonly class GetUserCollectionsHandler
{
    public function __construct(
        private CollectionApiInterface $apiClient,
    ) {
    }

    public function __invoke(GetUserCollectionsQuery $query): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->getAll(),
        );
    }
}
