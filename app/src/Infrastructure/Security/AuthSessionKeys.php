<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class AuthSessionKeys
{
    public const string ACCESS_TOKEN = 'auth.access_token';

    public const string REFRESH_TOKEN = 'auth.refresh_token';
}
