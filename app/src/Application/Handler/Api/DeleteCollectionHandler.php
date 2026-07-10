<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Command\Api\DeleteCollectionCommand;
use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\CollectionApiInterface;

final readonly class DeleteCollectionHandler
{
    public function __construct(
        private CollectionApiInterface $apiClient,
    ) {
    }

    public function __invoke(DeleteCollectionCommand $command): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->delete($command->id),
        );
    }
}
