<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Session
{
    public static function start(string $name, bool $secure): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($name);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        unset($_SESSION[$key]);
    }

    public static function regenerate(): bool
    {
        return session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}
