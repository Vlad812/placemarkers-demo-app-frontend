<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\DTO\Api\Payload\CollectionCreatePayload;

interface CollectionApiInterface
{
    public function getAll(): HttpApiResult;

    public function create(CollectionCreatePayload $payload): HttpApiResult;

    public function delete(string $id): HttpApiResult;
}
