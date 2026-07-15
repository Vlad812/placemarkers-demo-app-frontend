<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Api\CreateTagCommand;

final readonly class TagCreatePayload
{
    public function __construct(
        public string $name,
        public string $typeId,
        public ?string $description = null,
    ) {
    }

    public static function fromCommand(CreateTagCommand $command): self
    {
        return new self($command->name, $command->typeId, $command->description);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'name' => $this->name,
            'type_id' => $this->typeId,
        ];

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        return $payload;
    }
}
