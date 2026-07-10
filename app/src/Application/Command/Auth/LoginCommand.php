<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

use App\Application\Exception\FormValidationException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class LoginCommand
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        $email = (string) ($data['email'] ?? '');
        $password = (string) ($data['password'] ?? '');

        try {
            Assert::email($email, 'Неверный формат email.');
            Assert::notEmpty($password, 'Пароль не должен быть пустым.');
        } catch (InvalidArgumentException $e) {
            throw new FormValidationException($e->getMessage(), [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);
        }

        return new self($email, $password);
    }
}
