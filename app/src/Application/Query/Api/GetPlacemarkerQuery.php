<?php

declare(strict_types=1);

namespace App\Application\Query\Api;

use Webmozart\Assert\Assert;

final readonly class GetPlacemarkerQuery
{
    public function __construct(
        public string $id,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromRawValues(array $data): self
    {
        Assert::keyExists($data, 'id', 'Missing required parameter: id.');
        Assert::uuid($data['id'], 'Parameter id must be a valid UUID.');

        return new self((string) $data['id']);
    }
}
