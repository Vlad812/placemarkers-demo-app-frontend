<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Auth\SignupCommand;

final readonly class AuthSignupPayload
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public static function fromCommand(SignupCommand $command): self
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
