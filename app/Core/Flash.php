<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Flash
{
    public static function success(string $message): void
    {
        Session::set('flash_success', $message);
    }

    public static function error(string $message): void
    {
        Session::set('flash_error', $message);
    }

    public static function withInput(array $input): void
    {
        Session::set('old_input', $input);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!Session::has($key)) {
            return $default;
        }

        $value = Session::get($key);
        Session::remove($key);
        return $value;
    }
}
