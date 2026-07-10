<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

use App\Application\Exception\FormValidationException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class SignupCommand
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
        $confirmPassword = (string) ($data['confirm_password'] ?? '');

        try {
            Assert::email($email, 'Неверный формат email.');
            Assert::minLength($password, 8, 'Пароль должен быть не менее 8 символов.');
            Assert::eq($password, $confirmPassword, 'Пароли не совпадают.');
        } catch (InvalidArgumentException $e) {
            throw new FormValidationException($e->getMessage(), [
                'error' => $e->getMessage(),
                'success' => null,
                'email' => $email,
            ]);
        }

        return new self($email, $password);
    }
}
