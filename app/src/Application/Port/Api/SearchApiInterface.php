<?php

declare(strict_types=1);

namespace App\Application\Port\Api;

use App\Application\DTO\Api\HttpApiResult;

interface SearchApiInterface
{
    /**
     * @param list<string> $tags
     * @param list<string> $types
     */
    public function search(
        float $lat,
        float $lon,
        int $radiusMeters,
        array $tags = [],
        array $types = [],
    ): HttpApiResult;

    public function getUserTags(): HttpApiResult;

    public function getPlacemarkerTypes(): HttpApiResult;
}
