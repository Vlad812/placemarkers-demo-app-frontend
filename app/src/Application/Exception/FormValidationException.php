<?php

declare(strict_types=1);

namespace App\Application\Exception;

use InvalidArgumentException;

final class FormValidationException extends InvalidArgumentException implements ClientExceptionInterface
{
    public function __construct(
        string $message,
        private readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
