<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Command\Api\DeletePlacemarkerCommand;
use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\PlacemarkerApiInterface;

final readonly class DeletePlacemarkerHandler
{
    public function __construct(
        private PlacemarkerApiInterface $apiClient,
    ) {
    }

    public function __invoke(DeletePlacemarkerCommand $command): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult($this->apiClient->delete($command->id));
    }
}
