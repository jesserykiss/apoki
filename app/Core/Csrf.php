<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }

        return (string) Session::get('csrf_token');
    }

    public static function validate(mixed $token): bool
    {
        $sessionToken = Session::get('csrf_token');
        return is_string($token) && is_string($sessionToken) && hash_equals($sessionToken, $token);
    }
}
