<?php

declare(strict_types=1);

namespace Apoki\Core;

final class Response
{
    public function __construct(private readonly View $view)
    {
    }

    public function view(string $template, array $data = [], int $status = 200): string
    {
        http_response_code($status);
        return $this->view->render($template, $data);
    }

    public function redirect(string $url, int $status = 302): never
    {
        if (!headers_sent()) {
            header('Location: ' . $url, true, $status);
        }
        exit;
    }
}
