<?php

declare(strict_types=1);

namespace App\Application\Query\Api;

use Webmozart\Assert\Assert;

final readonly class SearchPlacemarkersQuery
{
    /**
     * @param list<string> $tags
     * @param list<string> $types
     */
    public function __construct(
        public float $lat,
        public float $lon,
        public int $radius,
        public array $tags = [],
        public array $types = [],
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

        return new self(
            (float) $lat,
            (float) $lon,
            (int) $radius,
            self::extractStringList($data, 'tags'),
            self::extractStringList($data, 'types'),
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<string>
     */
    private static function extractStringList(array $data, string $key): array
    {
        if (!array_key_exists($key, $data)) {
            return [];
        }

        $value = $data[$key];

        if (is_array($value)) {
            return array_values(array_filter(
                $value,
                static fn (mixed $item): bool => is_string($item) && $item !== '',
            ));
        }

        if (is_string($value) && $value !== '') {
            return [$value];
        }

        return [];
    }
}
