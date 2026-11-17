<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Router
{
    public function __construct(private readonly array $routes)
    {
    }

    public function match(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if (($route[0] ?? '') !== $method) {
                continue;
            }

            $params = [];
            if ($this->matchesPath((string) ($route[1] ?? ''), $path, $params)) {
                return [
                    'method' => $method,
                    'path' => $route[1],
                    'controller' => $route[2],
                    'action' => $route[3],
                    'auth' => !empty($route[4]),
                    'params' => $params,
                ];
            }
        }

        return null;
    }

    private function matchesPath(string $pattern, string $path, array &$params): bool
    {
        $patternParts = $pattern === '/' ? [] : explode('/', trim($pattern, '/'));
        $pathParts = $path === '/' ? [] : explode('/', trim($path, '/'));

        if (count($patternParts) !== count($pathParts)) {
            return false;
        }

        foreach ($patternParts as $index => $part) {
            if ($part !== '' && $part[0] === '{' && str_ends_with($part, '}')) {
                $name = substr($part, 1, -1);
                $params[$name] = rawurldecode((string) $pathParts[$index]);
                continue;
            }

            if ($part !== $pathParts[$index]) {
                return false;
            }
        }

        return true;
    }
}
