<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Request
{
    private array $query;
    private array $post;
    private array $server;

    public function __construct(
        ?array $query = null,
        ?array $post = null,
        ?array $server = null
    ) {
        $this->query = $query ?? $_GET;
        $this->post = $post ?? $_POST;
        $this->server = $server ?? $_SERVER;
    }

    public function getMethod(): string
    {
        return strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function getPath(): string
    {
        $path = parse_url((string) ($this->server['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $path = rtrim((string) ($path ?: '/'), '/');

        return $path === '' ? '/' : $path;
    }

    public function getPathWithQuery(): string
    {
        return (string) ($this->server['REQUEST_URI'] ?? '/');
    }

    public function query(string $key, mixed $default = ''): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = ''): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function allPost(): array
    {
        return $this->post;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = filter_var($this->query[$key] ?? $default, FILTER_VALIDATE_INT);
        return $value === false ? $default : $value;
    }

    public function postInt(string $key, int $default = 0): int
    {
        $value = filter_var($this->post[$key] ?? $default, FILTER_VALIDATE_INT);
        return $value === false ? $default : $value;
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function referer(string $fallback = '/'): string
    {
        return (string) ($this->server['HTTP_REFERER'] ?? $fallback);
    }
}
