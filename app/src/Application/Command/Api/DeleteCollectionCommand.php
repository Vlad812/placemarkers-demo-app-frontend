<?php

declare(strict_types=1);

namespace App\Application\Command\Api;

use Webmozart\Assert\Assert;

final readonly class DeleteCollectionCommand
{
    public function __construct(
        public string $id,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        Assert::keyExists($data, 'id', 'Missing id.');
        Assert::notEmpty($data['id'], 'Collection id is required.');

        return new self((string) $data['id']);
    }
}
