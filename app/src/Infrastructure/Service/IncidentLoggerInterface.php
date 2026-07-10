<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Throwable;

interface IncidentLoggerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function logWarning(string $message, Throwable $exception, array $context = []): string;

    /**
     * @param array<string, mixed> $context
     */
    public function logWarningMessage(string $message, array $context = []): string;

    /**
     * @param array<string, mixed> $context
     */
    public function logError(string $message, Throwable $exception, array $context = []): string;

    /**
     * @param array<string, mixed> $context
     */
    public function logErrorMessage(string $message, array $context = []): string;

    public function userMessage(string $incidentId): string;
}
