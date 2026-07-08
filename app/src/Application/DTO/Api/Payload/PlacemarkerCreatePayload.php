<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Api\CreatePlacemarkerCommand;

final readonly class PlacemarkerCreatePayload
{
    public function __construct(
        public string $name,
        public float $lat,
        public float $lon,
        public ?string $description = null,
        public ?string $typeId = null,
        public ?array $tags = null,
    ) {
    }

    public static function fromCommand(CreatePlacemarkerCommand $command): self
    {
        return new self(
            $command->name,
            $command->lat,
            $command->lon,
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
        $payload = [
            'name' => $this->name,
            'lat' => $this->lat,
            'lon' => $this->lon,
        ];

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
