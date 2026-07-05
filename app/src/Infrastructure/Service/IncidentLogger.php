<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Psr\Log\LoggerInterface;
use Random\RandomException;
use Throwable;

final readonly class IncidentLogger
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     * @throws RandomException
     */
    public function logWarning(string $message, Throwable $exception, array $context = []): string
    {
        return $this->log('warning', $message, $context, $exception);
    }

    /**
     * @param array<string, mixed> $context
     * @throws RandomException
     */
    public function logWarningMessage(string $message, array $context = []): string
    {
        return $this->log('warning', $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     * @throws RandomException
     */
    public function logError(string $message, Throwable $exception, array $context = []): string
    {
        return $this->log('error', $message, $context, $exception);
    }

    /**
     * @param array<string, mixed> $context
     * @throws RandomException
     */
    public function logErrorMessage(string $message, array $context = []): string
    {
        return $this->log('error', $message, $context);
    }

    public function userMessage(string $incidentId): string
    {
        return sprintf('Произошла внутренняя ошибка. Incident ID: %s', $incidentId);
    }

    /**
     * @param array<string, mixed> $context
     * @throws RandomException
     */
    private function log(string $level, string $message, array $context = [], ?Throwable $exception = null): string
    {
        $incidentId = $this->generateIncidentId();

        $this->logger->log(
            $level,
            sprintf('%s Incident ID: [%s].', $message, $incidentId),
            array_merge($context, [
                'incident_id' => $incidentId,
                'exception' => $exception,
            ]),
        );

        return $incidentId;
    }

    /**
     * @throws RandomException
     */
    private function generateIncidentId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($bytes), 4),
        );
    }
}
