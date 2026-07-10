<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

use App\Application\Exception\FormValidationException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ForgotPasswordCommand
{
    public function __construct(
        public string $email,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        $email = (string) ($data['email'] ?? '');

        try {
            Assert::email($email, 'Неверный формат email.');
        } catch (InvalidArgumentException $e) {
            throw new FormValidationException($e->getMessage(), [
                'error' => $e->getMessage(),
                'success' => null,
                'email' => $email,
            ]);
        }

        return new self($email);
    }
}
