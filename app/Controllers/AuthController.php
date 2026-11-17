<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Auth;
use Apoki\Core\Flash;
use Apoki\Core\Request;
use Apoki\Core\Response;
use Apoki\Core\Session;
use Apoki\Core\ValidationException;
use Apoki\Services\AuthService;
use DomainException;

final class AuthController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        private readonly AuthService $authService
    ) {
        parent::__construct($request, $response);
    }

    public function login(Request $request, array $params): string
    {
        if (Auth::check()) {
            $this->response->redirect('/');
        }

        return $this->response->view('auth/login', [
            'layout' => 'auth',
            'title' => '登录',
            'next' => $this->safeNext((string) $request->query('next', '/')),
        ]);
    }

    public function authenticate(Request $request, array $params): never
    {
        $this->requireCsrf('/login');

        try {
            $user = $this->authService->authenticate(
                trim((string) $request->input('username')),
                (string) $request->input('password')
            );
        } catch (DomainException $exception) {
            Flash::error($exception->getMessage());
            Flash::withInput(['username' => (string) $request->input('username')]);
            $this->response->redirect('/login');
        }

        Auth::login($user);
        Flash::success('登录成功。');
        $this->response->redirect($this->safeNext((string) $request->input('next', '/')));
    }

    public function register(Request $request, array $params): string
    {
        if (Auth::check()) {
            $this->response->redirect('/');
        }

        return $this->response->view('auth/register', [
            'layout' => 'auth',
            'title' => '注册',
        ]);
    }

    public function store(Request $request, array $params): never
    {
        $this->requireCsrf('/register');

        try {
            $user = $this->authService->register($request->allPost());
        } catch (ValidationException $exception) {
            $this->validationError($exception, '/register');
        } catch (DomainException $exception) {
            $this->businessError($exception, '/register');
        }

        Auth::login($user);
        Flash::success('注册成功。');
        $this->response->redirect('/');
    }

    public function logout(Request $request, array $params): never
    {
        $this->requireCsrf('/');
        Auth::logout();
        $this->response->redirect('/login');
    }

    public function password(Request $request, array $params): string
    {
        return $this->response->view('auth/password', [
            'title' => '修改密码',
            'active' => 'password',
        ]);
    }

    public function updatePassword(Request $request, array $params): never
    {
        $this->requireCsrf('/password');

        try {
            $updatedAt = $this->authService->changePassword(
                Auth::id(),
                (string) $request->input('current_password'),
                (string) $request->input('new_password'),
                (string) $request->input('confirm_password')
            );
        } catch (ValidationException $exception) {
            $this->validationError($exception, '/password');
        } catch (DomainException $exception) {
            $this->businessError($exception, '/password');
        }

        $user = Auth::user() ?? [];
        $user['updated_at'] = $updatedAt;
        Session::regenerate();
        Session::set('auth_user', $user);
        Flash::success('密码已修改。');
        $this->response->redirect('/password');
    }

    private function safeNext(string $next): string
    {
        if ($next === '' || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            return '/';
        }

        return $next;
    }
}
