<?php

declare(strict_types=1);

namespace App\Application\Command\Api;

use Webmozart\Assert\Assert;

final readonly class SaveCollectionCommand
{
    public function __construct(
        public string $name,
        public array $searchCriteria,
        public array $placemarkers,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        Assert::keyExists($data, 'name', 'Missing collection name.');
        Assert::stringNotEmpty(trim((string) $data['name']), 'Collection name must not be empty.');
        Assert::maxLength(trim((string) $data['name']), 255);

        Assert::keyExists($data, 'search_criteria', 'Missing search criteria.');
        Assert::isArray($data['search_criteria']);

        Assert::keyExists($data['search_criteria'], 'latitude', 'Missing search latitude.');
        Assert::numeric($data['search_criteria']['latitude'], 'Search latitude must be numeric.');
        Assert::greaterThanEq((float) $data['search_criteria']['latitude'], -90.0);
        Assert::lessThanEq((float) $data['search_criteria']['latitude'], 90.0);

        Assert::keyExists($data['search_criteria'], 'longitude', 'Missing search longitude.');
        Assert::numeric($data['search_criteria']['longitude'], 'Search longitude must be numeric.');
        Assert::greaterThanEq((float) $data['search_criteria']['longitude'], -180.0);
        Assert::lessThanEq((float) $data['search_criteria']['longitude'], 180.0);

        Assert::keyExists($data['search_criteria'], 'radius', 'Missing search radius.');
        Assert::numeric($data['search_criteria']['radius'], 'Search radius must be numeric.');
        Assert::greaterThan((float) $data['search_criteria']['radius'], 0.0, 'Search radius must be greater than zero.');

        Assert::keyExists($data, 'placemarkers', 'Missing placemarkers.');
        Assert::isArray($data['placemarkers']);
        Assert::notEmpty($data['placemarkers'], 'Placemarkers list must not be empty.');

        return new self(
            trim((string) $data['name']),
            $data['search_criteria'],
            $data['placemarkers'],
        );
    }
}
