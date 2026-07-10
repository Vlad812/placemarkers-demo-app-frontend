<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Command\Api\CreatePlacemarkerCommand;
use App\Application\DTO\Api\Payload\PlacemarkerCreatePayload;
use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\PlacemarkerApiInterface;

final readonly class CreatePlacemarkerHandler
{
    public function __construct(
        private PlacemarkerApiInterface $apiClient,
    ) {
    }

    public function __invoke(CreatePlacemarkerCommand $command): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->create(PlacemarkerCreatePayload::fromCommand($command)),
        );
    }
}
