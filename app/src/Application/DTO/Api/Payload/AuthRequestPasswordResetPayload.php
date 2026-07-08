<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Auth\ForgotPasswordCommand;

final readonly class AuthRequestPasswordResetPayload
{
    public function __construct(
        public string $email,
    ) {
    }

    public static function fromCommand(ForgotPasswordCommand $command): self
    {
        return new self($command->email);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
