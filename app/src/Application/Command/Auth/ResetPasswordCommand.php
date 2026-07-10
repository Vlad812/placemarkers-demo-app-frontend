<?php

declare(strict_types=1);

namespace App\Application\Command\Auth;

use App\Application\Exception\FormValidationException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ResetPasswordCommand
{
    public function __construct(
        public string $token,
        public string $password,
    ) {
    }

    /**
     * @param array $data
     * @return self
     */
    public static function fromRawValues(array $data): self
    {
        $token = (string) ($data['token'] ?? '');

        try {
            Assert::notEmpty($token, 'Token is required.');
        } catch (InvalidArgumentException $e) {
            throw new FormValidationException($e->getMessage(), [
                'token' => $token,
                'error' => $e->getMessage(),
                'success' => null,
            ]);
        }

        $password = (string) ($data['password'] ?? '');
        $confirmPassword = (string) ($data['confirm_password'] ?? '');

        try {
            Assert::minLength($password, 8, 'Пароль должен быть не менее 8 символов.');
            Assert::eq($password, $confirmPassword, 'Пароли не совпадают.');
        } catch (InvalidArgumentException $e) {
            throw new FormValidationException($e->getMessage(), [
                'token' => $token,
                'error' => $e->getMessage(),
                'success' => null,
            ]);
        }

        return new self($token, $password);
    }
}
