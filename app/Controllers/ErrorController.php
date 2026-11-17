<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Response;

final class ErrorController
{
    public function __construct(private readonly Response $response)
    {
    }

    public function notFound(): string
    {
        return $this->response->view('errors/404', [
            'title' => '未找到',
            'active' => '',
        ], 404);
    }
}
