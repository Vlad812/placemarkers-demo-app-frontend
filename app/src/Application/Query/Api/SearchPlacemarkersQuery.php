<?php

declare(strict_types=1);

namespace App\Application\Query\Api;

use Webmozart\Assert\Assert;

final readonly class SearchPlacemarkersQuery
{
    /**
     * @param list<array{type_id: string, tags: list<string>}> $filters
     */
    public function __construct(
        public float $lat,
        public float $lon,
        public int $radius,
        public array $filters = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromRawValues(array $data): self
    {
        Assert::keyExists($data, 'lat', 'Missing required parameter: lat.');
        Assert::keyExists($data, 'lon', 'Missing required parameter: lon.');
        Assert::keyExists($data, 'radius', 'Missing required parameter: radius.');

        $lat = $data['lat'];
        $lon = $data['lon'];
        $radius = $data['radius'];

        Assert::numeric($lat, 'Latitude must be numeric.');
        Assert::greaterThanEq((float) $lat, -90.0);
        Assert::lessThanEq((float) $lat, 90.0);

        Assert::numeric($lon, 'Longitude must be numeric.');
        Assert::greaterThanEq((float) $lon, -180.0);
        Assert::lessThanEq((float) $lon, 180.0);

        Assert::numeric($radius, 'Radius must be numeric.');
        Assert::greaterThan((float) $radius, 0.0, 'Search radius must be greater than zero.');

        Assert::keyExists($data, 'filters', 'Missing required parameter: filters.');

        $filters = $data['filters'];
        if (is_string($filters)) {
            $filters = $filters === '' ? [] : json_decode($filters, true);
        }
        Assert::isArray($filters, 'Filters must be an array.');

        return new self(
            (float) $lat,
            (float) $lon,
            (int) $radius,
            self::normalizeFilters($filters),
        );
    }

    /**
     * Example $value:
     * [
     *     ['type_id' => 'parking', 'tags' => ['a0f89579-cd7f-4d81-9aee-0512f756957e']],
     *     ['type_id' => 'marketplace', 'tags' => []],
     * ]
     * or [] when no filters are selected.
     *
     * @param list<array{type_id: string, tags: list<string>}> $value
     * @return list<array{type_id: string, tags: list<string>}>
     */
    private static function normalizeFilters(array $value): array
    {
        $filters = [];

        foreach ($value as $index => $item) {
            Assert::isArray($item, sprintf('Filter at index %d must be an object.', $index));

            Assert::keyExists($item, 'type_id', sprintf('Filter at index %d is missing type_id.', $index));
            Assert::stringNotEmpty($item['type_id'], sprintf('Filter at index %d type_id must not be empty.', $index));

            $tags = $item['tags'] ?? [];
            Assert::isArray($tags, sprintf('Filter at index %d tags must be an array.', $index));
            Assert::allStringNotEmpty($tags, sprintf('Filter at index %d tags must be non-empty strings.', $index));

            $filters[] = [
                'type_id' => $item['type_id'],
                'tags' => array_values($tags),
            ];
        }

        return $filters;
    }
}
