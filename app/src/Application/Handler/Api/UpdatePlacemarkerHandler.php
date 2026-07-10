<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Command\Api\UpdatePlacemarkerCommand;
use App\Application\DTO\Api\Payload\PlacemarkerUpdatePayload;
use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\PlacemarkerApiInterface;

final readonly class UpdatePlacemarkerHandler
{
    public function __construct(
        private PlacemarkerApiInterface $apiClient,
    ) {
    }

    public function __invoke(UpdatePlacemarkerCommand $command): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->update($command->id, PlacemarkerUpdatePayload::fromCommand($command)),
        );
    }
}
