<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Auth\LoginCommand;

final readonly class AuthLoginPayload
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public static function fromCommand(LoginCommand $command): self
    {
        return new self($command->email, $command->password);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
