<?php

declare(strict_types=1);

namespace App\Application\Command\Api;

use Webmozart\Assert\Assert;

final readonly class UpdatePlacemarkerCommand
{
    public function __construct(
        public string $id,
        public string $name,
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
        Assert::keyExists($data, 'id', 'Missing id.');
        Assert::uuid($data['id']);
        $id = (string) $data['id'];

        Assert::keyExists($data, 'name', 'Missing name.');
        Assert::stringNotEmpty(trim((string) $data['name']), 'Placemarker name must not be empty.');
        Assert::maxLength(trim((string) $data['name']), 255);

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
            $id,
            trim((string) $data['name']),
            $description,
            $typeId,
            $tags,
        );
    }
}
