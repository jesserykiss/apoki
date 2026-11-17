<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Csrf;
use Apoki\Core\Flash;
use Apoki\Core\Request;
use Apoki\Core\Response;
use Apoki\Core\ValidationException;

abstract class Controller
{
    public function __construct(
        protected readonly Request $request,
        protected readonly Response $response
    ) {
    }

    protected function requireCsrf(string $fallback): void
    {
        if (!Csrf::validate($this->request->input('_token'))) {
            Flash::error('页面已过期，请重试。');
            Flash::withInput($this->safeInput());
            $this->response->redirect($fallback);
        }
    }

    protected function validationError(ValidationException $exception, string $fallback): never
    {
        Flash::error(implode(' ', array_unique($exception->errors())));
        Flash::withInput($this->safeInput());
        $this->response->redirect($fallback);
    }

    protected function businessError(\Throwable $exception, string $fallback): never
    {
        Flash::error($exception->getMessage());
        Flash::withInput($this->safeInput());
        $this->response->redirect($fallback);
    }

    protected function idFromParams(array $params): int
    {
        return (int) ($params['id'] ?? 0);
    }

    private function safeInput(): array
    {
        return array_diff_key(
            $this->request->allPost(),
            array_flip(['password', 'current_password', 'new_password', 'confirm_password'])
        );
    }
}
