<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class SessionInvalidatedEvent extends Event
{
}
