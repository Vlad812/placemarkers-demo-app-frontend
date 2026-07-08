<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Auth\ResetPasswordCommand;

final readonly class AuthResetPasswordPayload
{
    public function __construct(
        public string $token,
        public string $password,
    ) {
    }

    public static function fromCommand(ResetPasswordCommand $command): self
    {
        return new self($command->token, $command->password);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'password' => $this->password,
        ];
    }
}
