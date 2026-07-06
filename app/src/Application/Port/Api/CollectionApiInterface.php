<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\HttpApiResult;

interface CollectionApiInterface
{
    public function getAll(): HttpApiResult;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): HttpApiResult;

    public function delete(string $id): HttpApiResult;
}
