<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

use Webmozart\Assert\Assert;

final readonly class ConfirmEmailCommand
{
    public function __construct(
        public string $token,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        $token = (string) ($data['token'] ?? '');
        Assert::notEmpty($token, 'Token is required.');

        return new self($token);
    }
}
