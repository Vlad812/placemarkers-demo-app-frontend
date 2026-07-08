<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Api\UpdatePlacemarkerCommand;

final readonly class PlacemarkerUpdatePayload
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $typeId = null,
        public ?array $tags = null,
    ) {
    }

    public static function fromCommand(UpdatePlacemarkerCommand $command): self
    {
        return new self(
            $command->name,
            $command->description,
            $command->typeId,
            $command->tags,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['name' => $this->name];

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        if ($this->typeId !== null) {
            $payload['type_id'] = $this->typeId;
        }

        if ($this->tags !== null) {
            $payload['tags'] = $this->tags;
        }

        return $payload;
    }
}
