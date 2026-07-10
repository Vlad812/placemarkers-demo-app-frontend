<?php

declare(strict_types=1);

namespace App\Application\Command\Api;

use Webmozart\Assert\Assert;

final readonly class CreateTagCommand
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        Assert::keyExists($data, 'name', 'Missing name.');
        Assert::stringNotEmpty(trim((string) $data['name']), 'Tag name must not be empty.');
        Assert::maxLength(trim((string) $data['name']), 255);

        $description = null;
        if (isset($data['description'])) {
            Assert::string($data['description']);
            $description = $data['description'];
        }

        return new self(trim((string) $data['name']), $description);
    }
}
