<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Exception\UnauthorizedException;
use App\Infrastructure\Security\User;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class AuthenticatedUserProvider
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function getUserUuid(): string
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new UnauthorizedException('Unauthorized.');
        }

        /** @var User $user */
        $user = $this->security->getUser();

        return $user->getUuid();
    }

    public function isAuthenticated(): bool
    {
        return $this->security->isGranted('IS_AUTHENTICATED_FULLY');
    }
}
