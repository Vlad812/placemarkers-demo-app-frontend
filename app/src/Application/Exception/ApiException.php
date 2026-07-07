<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

final class ApiException extends RuntimeException implements ClientExceptionInterface
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
