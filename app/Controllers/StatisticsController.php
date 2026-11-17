<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Auth;
use Apoki\Core\Request;
use Apoki\Core\Response;
use Apoki\Services\StatisticsService;

final class StatisticsController extends Controller
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
        $filters = [
            'direction' => (string) $request->query('direction'),
            'relative_id' => (string) $request->query('relative_id'),
            'banquet_id' => (string) $request->query('banquet_id'),
            'type_id' => (string) $request->query('type_id'),
            'date_from' => (string) $request->query('date_from'),
            'date_to' => (string) $request->query('date_to'),
        ];
        $data = $this->statisticsService->index(Auth::id(), $filters);
        $data['title'] = '统计';
        $data['active'] = 'statistics';

        return $this->response->view('statistics/index', $data);
    }
}
