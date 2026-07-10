<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Command\Api\SaveCollectionCommand;
use App\Application\DTO\Api\Payload\CollectionCreatePayload;
use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\CollectionApiInterface;

final readonly class SaveCollectionHandler
{
    public function __construct(
        private CollectionApiInterface $apiClient,
    ) {
    }

    public function __invoke(SaveCollectionCommand $command): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->create(CollectionCreatePayload::fromCommand($command)),
        );
    }
}
