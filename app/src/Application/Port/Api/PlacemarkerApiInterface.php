<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\HttpApiResult;

interface PlacemarkerApiInterface
{
    public function getAll(): HttpApiResult;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): HttpApiResult;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): HttpApiResult;

    public function delete(string $id): HttpApiResult;

    /**
     * @param array<string, mixed> $data
     */
    public function createTag(array $data): HttpApiResult;
}
