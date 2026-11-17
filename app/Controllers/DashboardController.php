<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Auth;
use Apoki\Core\Request;
use Apoki\Core\Response;
use Apoki\Services\StatisticsService;

final class DashboardController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        private readonly StatisticsService $statisticsService
    ) {
        parent::__construct($request, $response);
    }

    public function index(Request $request, array $params): string
    {
        $data = $this->statisticsService->dashboard(Auth::id());
        $data['title'] = '看板';
        $data['active'] = 'dashboard';

        return $this->response->view('dashboard/index', $data);
    }
}
