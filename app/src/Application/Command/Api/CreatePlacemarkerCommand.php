<?php

declare(strict_types=1);

namespace App\Application\Command\Api;

use Webmozart\Assert\Assert;

final readonly class CreatePlacemarkerCommand
{
    public function __construct(
        public string $name,
        public float $lat,
        public float $lon,
        public ?string $description = null,
        public ?string $typeId = null,
        public ?array $tags = null,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        Assert::keyExists($data, 'name', 'Missing name.');
        Assert::stringNotEmpty(trim((string) $data['name']), 'Placemarker name must not be empty.');
        Assert::maxLength(trim((string) $data['name']), 255);

        Assert::keyExists($data, 'lat', 'Missing latitude.');
        Assert::numeric($data['lat'], 'Latitude must be numeric.');
        Assert::greaterThanEq((float) $data['lat'], -90.0);
        Assert::lessThanEq((float) $data['lat'], 90.0);

        Assert::keyExists($data, 'lon', 'Missing longitude.');
        Assert::numeric($data['lon'], 'Longitude must be numeric.');
        Assert::greaterThanEq((float) $data['lon'], -180.0);
        Assert::lessThanEq((float) $data['lon'], 180.0);

        $description = null;

        if (isset($data['description'])) {
            Assert::string($data['description']);
            Assert::maxLength(trim($data['description']), 2000);
            $description = $data['description'];
        }

        $typeId = null;

        if (array_key_exists('type_id', $data) && $data['type_id'] !== null) {
            Assert::string($data['type_id']);
            $typeId = $data['type_id'];
        }

        $tags = null;

        if (array_key_exists('tags', $data) && $data['tags'] !== null) {
            Assert::isArray($data['tags']);
            $tags = $data['tags'];
        }

        return new self(
            trim((string) $data['name']),
            (float) $data['lat'],
            (float) $data['lon'],
            $description,
            $typeId,
            $tags,
        );
    }
}
