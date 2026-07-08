<?php

declare(strict_types=1);

namespace App\Application\DTO\Api\Payload;

use App\Application\Command\Api\SaveCollectionCommand;

final readonly class CollectionCreatePayload
{
    public function __construct(
        public string $name,
        public array $searchCriteria,
        public array $placemarkers,
    ) {
    }

    public static function fromCommand(SaveCollectionCommand $command): self
    {
        return new self(
            $command->name,
            $command->searchCriteria,
            $command->placemarkers,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'search_criteria' => $this->searchCriteria,
            'placemarkers' => $this->placemarkers,
        ];
    }
}
