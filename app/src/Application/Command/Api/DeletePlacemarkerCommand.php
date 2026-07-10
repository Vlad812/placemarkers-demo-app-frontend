<?php

declare(strict_types=1);

namespace App\Application\Command\Api;

use Webmozart\Assert\Assert;

final readonly class DeletePlacemarkerCommand
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
        Assert::uuid($data['id']);

        return new self((string) $data['id']);
    }
}
