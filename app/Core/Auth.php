<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Auth
{
    public static function check(): bool
    {
        return is_array(Session::get('auth_user')) && !empty(Session::get('auth_user')['id']);
    }

    public static function id(): int
    {
        return (int) (Session::get('auth_user')['id'] ?? 0);
    }

    public static function user(): ?array
    {
        $user = Session::get('auth_user');
        return is_array($user) ? $user : null;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        unset($user['password_hash']);
        Session::set('auth_user', $user);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
