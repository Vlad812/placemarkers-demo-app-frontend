<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\HttpApiResult;

interface SearchApiInterface
{
    /**
     * @param list<array{type_id: string, tags: list<string>}> $filters
     */
    public function search(
        float $lat,
        float $lon,
        int $radiusMeters,
        array $filters = [],
    ): HttpApiResult;

    public function getUserTags(): HttpApiResult;

    public function getPlacemarkerTypes(): HttpApiResult;

    public function getRecent(int $limit): HttpApiResult;

    public function getPlacemarker(string $id): HttpApiResult;
}
