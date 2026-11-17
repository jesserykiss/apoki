<?php

declare(strict_types=1);

namespace Apoki\Core;

use Apoki\Controllers\AuthController;
use Apoki\Controllers\BanquetController;
use Apoki\Controllers\DashboardController;
use Apoki\Controllers\ErrorController;
use Apoki\Controllers\GiftRecordController;
use Apoki\Controllers\RelativeController;
use Apoki\Controllers\StatisticsController;
use Apoki\DAO\BanquetDao;
use Apoki\DAO\BanquetTypeDao;
use Apoki\DAO\GiftRecordDao;
use Apoki\DAO\RelativeDao;
use Apoki\DAO\StatisticsDao;
use Apoki\DAO\UserDao;
use Apoki\Services\AuthService;
use Apoki\Services\BanquetService;
use Apoki\Services\GiftRecordService;
use Apoki\Services\RelativeService;
use Apoki\Services\StatisticsService;
use Throwable;

final class Bootstrap
{
    private const ROUTE_CONFIG = '/config/routes.php';

    public static function run(): void
    {
        self::defineConstants();
        self::registerAutoload();

        $request = new Request();
        $view = new View();
        $response = new Response($view);
        $config = [];

        try {
            $config = self::loadConfig();
            self::configureEnvironment($config);
            Session::start(
                (string) ($config['app']['session_name'] ?? 'apoki_session'),
                (bool) ($config['app']['secure_cookie'] ?? false)
            );
            Database::configure((array) $config['db']);
            $registry = self::buildControllers($request, $response);
            self::dispatch($request, $response, $registry['controllers'], $registry['authService']);
        } catch (Throwable $exception) {
            self::renderError($response, $config, $exception);
        }
    }

    private static function defineConstants(): void
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 2));
        }
        if (!defined('APP_PATH')) {
            define('APP_PATH', BASE_PATH . '/app');
        }
        if (!defined('CONFIG_PATH')) {
            define('CONFIG_PATH', BASE_PATH . '/config');
        }
        if (!defined('VIEW_PATH')) {
            define('VIEW_PATH', APP_PATH . '/Views');
        }
    }

    private static function registerAutoload(): void
    {
        spl_autoload_register(static function (string $class): void {
            $prefix = 'Apoki\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $file = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';

            if (is_file($file)) {
                require $file;
            }
        });
    }

    private static function loadConfig(): array
    {
        $file = CONFIG_PATH . '/config.php';
        if (!is_file($file)) {
            throw new \RuntimeException('缺少 config/config.php，请从 config.example.php 创建配置。');
        }

        $config = require $file;
        return is_array($config) ? $config : [];
    }

    private static function configureEnvironment(array $config): void
    {
        date_default_timezone_set((string) ($config['app']['timezone'] ?? 'Asia/Shanghai'));
        ini_set('display_errors', !empty($config['app']['debug']) ? '1' : '0');
        error_reporting(E_ALL);
    }

    private static function buildControllers(Request $request, Response $response): array
    {
        $database = Database::connection();
        $userDao = new UserDao($database);
        $relativeDao = new RelativeDao($database);
        $typeDao = new BanquetTypeDao($database);
        $banquetDao = new BanquetDao($database);
        $giftRecordDao = new GiftRecordDao($database);
        $statisticsDao = new StatisticsDao($database);

        $authService = new AuthService($userDao);
        $relativeService = new RelativeService($relativeDao, $giftRecordDao);
        $banquetService = new BanquetService($banquetDao, $typeDao, $relativeDao, $giftRecordDao);
        $giftRecordService = new GiftRecordService($giftRecordDao, $banquetDao, $relativeDao, $typeDao);
        $statisticsService = new StatisticsService($statisticsDao, $relativeDao, $typeDao);

        return [
            'controllers' => [
                'AuthController' => new AuthController($request, $response, $authService),
                'DashboardController' => new DashboardController($request, $response, $statisticsService),
                'RelativeController' => new RelativeController($request, $response, $relativeService),
                'BanquetController' => new BanquetController($request, $response, $banquetService),
                'GiftRecordController' => new GiftRecordController($request, $response, $giftRecordService),
                'StatisticsController' => new StatisticsController($request, $response, $statisticsService),
                'ErrorController' => new ErrorController($response),
            ],
            'authService' => $authService,
        ];
    }

    private static function dispatch(
        Request $request,
        Response $response,
        array $controllers,
        AuthService $authService
    ): void
    {
        $routes = require BASE_PATH . self::ROUTE_CONFIG;
        $router = new Router(is_array($routes) ? $routes : []);
        $route = $router->match($request->getMethod(), $request->getPath());

        if ($route === null) {
            echo $controllers['ErrorController']->notFound();
            return;
        }

        if (!empty($route['auth'])) {
            $sessionValid = Auth::check() && $authService->sessionIsValid(
                Auth::id(),
                (string) (Auth::user()['updated_at'] ?? '')
            );

            if (!$sessionValid) {
                Flash::error('请先登录后继续操作。');
                if (Auth::check()) {
                    Session::remove('auth_user');
                }
                $next = rawurlencode(self::loginReturnPath($request));
                $response->redirect('/login?next=' . $next);
            }
        }

        $controller = $controllers[$route['controller']] ?? null;
        if (!$controller || !method_exists($controller, $route['action'])) {
            echo $controllers['ErrorController']->notFound();
            return;
        }

        $result = $controller->{$route['action']}($request, $route['params']);
        if (is_string($result)) {
            echo $result;
        }
    }

    private static function renderError(Response $response, array $config, Throwable $exception): void
    {
        error_log((string) $exception);

        $data = [
            'layout' => 'app',
            'title' => '系统错误',
            'active' => '',
        ];

        if (!empty($config['app']['debug'])) {
            $data['message'] = $exception->getMessage();
            $data['trace'] = $exception->getTraceAsString();
        }

        echo $response->view('errors/500', $data, 500);
    }

    private static function loginReturnPath(Request $request): string
    {
        if ($request->getMethod() !== 'POST') {
            return $request->getPathWithQuery();
        }

        $referer = $request->referer('');
        if ($referer === '') {
            return '/';
        }

        $parts = parse_url($referer);
        if (!is_array($parts) || empty($parts['path'])) {
            return '/';
        }

        $path = $parts['path'];
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return '/';
        }

        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        return $path;
    }
}
