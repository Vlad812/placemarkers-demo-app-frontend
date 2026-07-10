<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Command\Api\CreateTagCommand;
use App\Application\DTO\Api\Payload\TagCreatePayload;
use App\Application\DTO\ApiProxyResponse;
use App\Application\Port\Api\PlacemarkerApiInterface;

final readonly class CreateTagHandler
{
    public function __construct(
        private PlacemarkerApiInterface $apiClient,
    ) {
    }

    public function __invoke(CreateTagCommand $command): ApiProxyResponse
    {
        return ApiProxyResponse::fromClientResult(
            $this->apiClient->createTag(TagCreatePayload::fromCommand($command)),
        );
    }
}
