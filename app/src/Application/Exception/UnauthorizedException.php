<?php

declare(strict_types=1);

namespace App\Application\Exception;

use RuntimeException;

final class UnauthorizedException extends RuntimeException implements ClientExceptionInterface
{
}
