<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

final class ServiceUnavailableException extends RuntimeException implements ClientExceptionInterface
{
    public const string DEFAULT_MESSAGE = 'Сервис авторизации временно недоступен. Попробуйте позже.';

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message);
    }
}
