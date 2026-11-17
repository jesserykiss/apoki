<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Auth;
use Apoki\Core\NotFoundException;
use Apoki\Core\Request;
use Apoki\Core\Response;
use Apoki\Core\ValidationException;
use Apoki\Services\GiftRecordService;

final class GiftRecordController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        private readonly GiftRecordService $giftRecordService
    ) {
        parent::__construct($request, $response);
    }

    public function index(Request $request, array $params): string
    {
        $filters = [
            'keyword' => trim((string) $request->query('keyword')),
            'direction' => (string) $request->query('direction'),
            'relative_id' => (string) $request->query('relative_id'),
            'banquet_id' => (string) $request->query('banquet_id'),
            'gift_type' => (string) $request->query('gift_type'),
            'type_id' => (string) $request->query('type_id'),
            'date_from' => (string) $request->query('date_from'),
            'date_to' => (string) $request->query('date_to'),
        ];
        $page = max(1, $request->int('page', 1));
        $result = $this->giftRecordService->paginate(Auth::id(), $filters, $page);
        $formData = $this->giftRecordService->formData(Auth::id());
        $result['filters'] = $filters;
        $result['relatives'] = $formData['relatives'];
        $result['banquets'] = $formData['banquets'];
        $result['types'] = $formData['types'];
        $result['title'] = '礼金明细';
        $result['active'] = 'gifts';

        return $this->response->view('gifts/index', $result);
    }

    public function create(Request $request, array $params): string
    {
        $data = $this->giftRecordService->formData(
            Auth::id(),
            null,
            $request->int('banquet_id', 0)
        );
        $data['title'] = '新增礼金';
        $data['active'] = 'gifts';

        return $this->response->view('gifts/form', $data);
    }

    public function store(Request $request, array $params): never
    {
        $fallback = '/gifts/create';
        if ($request->input('banquet_id')) {
            $fallback .= '?banquet_id=' . rawurlencode((string) $request->input('banquet_id'));
        }
        $this->requireCsrf($fallback);

        try {
            $this->giftRecordService->create(Auth::id(), $request->allPost());
        } catch (ValidationException $exception) {
            $this->validationError($exception, $fallback);
        }

        \Apoki\Core\Flash::success('礼金记录已添加。');
        $this->response->redirect('/gifts');
    }

    public function edit(Request $request, array $params): string
    {
        $id = $this->idFromParams($params);
        $data = $this->giftRecordService->formData(Auth::id(), $id);
        if ($data['gift'] === null) {
            return $this->response->view('errors/404', [
                'title' => '未找到',
                'active' => 'gifts',
            ], 404);
        }

        $data['title'] = '编辑礼金';
        $data['active'] = 'gifts';

        return $this->response->view('gifts/form', $data);
    }

    public function update(Request $request, array $params): never
    {
        $id = $this->idFromParams($params);
        $fallback = '/gifts/' . $id . '/edit';
        $this->requireCsrf($fallback);

        try {
            $this->giftRecordService->update(Auth::id(), $id, $request->allPost());
        } catch (NotFoundException $exception) {
            $this->notFoundResponse();
        } catch (ValidationException $exception) {
            $this->validationError($exception, $fallback);
        }

        \Apoki\Core\Flash::success('礼金记录已更新。');
        $this->response->redirect('/gifts');
    }

    public function destroy(Request $request, array $params): never
    {
        $id = $this->idFromParams($params);
        $this->requireCsrf('/gifts');

        try {
            $this->giftRecordService->delete(Auth::id(), $id);
        } catch (NotFoundException $exception) {
            $this->notFoundResponse();
        }

        \Apoki\Core\Flash::success('礼金记录已删除。');
        $this->response->redirect('/gifts');
    }

    private function notFoundResponse(): never
    {
        echo $this->response->view('errors/404', [
            'title' => '未找到',
            'active' => 'gifts',
        ], 404);
        exit;
    }
}
