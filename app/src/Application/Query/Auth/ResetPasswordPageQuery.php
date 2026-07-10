<?php

declare(strict_types=1);

namespace App\Application\Query\Auth;

use Webmozart\Assert\Assert;

final readonly class ResetPasswordPageQuery
{
    public function __construct(
        public string $token,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromRawValues(array $data): self
    {
        $token = (string) ($data['token'] ?? '');
        Assert::notEmpty($token, 'Token is required.');

        return new self($token);
    }
}
