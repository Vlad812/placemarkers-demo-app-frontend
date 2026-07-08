<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\DTO\Api\Payload\PlacemarkerCreatePayload;
use App\Application\DTO\Api\Payload\PlacemarkerUpdatePayload;
use App\Application\DTO\Api\Payload\TagCreatePayload;

interface PlacemarkerApiInterface
{
    public function getAll(): HttpApiResult;

    public function create(PlacemarkerCreatePayload $payload): HttpApiResult;

    public function update(string $id, PlacemarkerUpdatePayload $payload): HttpApiResult;

    public function delete(string $id): HttpApiResult;

    public function createTag(TagCreatePayload $payload): HttpApiResult;
}
