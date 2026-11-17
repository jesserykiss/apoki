<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Auth;
use Apoki\Core\NotFoundException;
use Apoki\Core\Request;
use Apoki\Core\Response;
use Apoki\Core\ValidationException;
use Apoki\Services\BanquetService;
use DomainException;

final class BanquetController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        private readonly BanquetService $banquetService
    ) {
        parent::__construct($request, $response);
    }

    public function index(Request $request, array $params): string
    {
        $filters = [
            'keyword' => trim((string) $request->query('keyword')),
            'type_id' => (string) $request->query('type_id'),
            'host_type' => (string) $request->query('host_type'),
            'date_from' => (string) $request->query('date_from'),
            'date_to' => (string) $request->query('date_to'),
        ];
        $page = max(1, $request->int('page', 1));
        $result = $this->banquetService->paginate(Auth::id(), $filters, $page);
        $result['filters'] = $filters;
        $result['types'] = $this->banquetService->types();
        $result['title'] = '宴请';
        $result['active'] = 'banquets';

        return $this->response->view('banquets/index', $result);
    }

    public function create(Request $request, array $params): string
    {
        return $this->form(Auth::id(), null);
    }

    public function store(Request $request, array $params): never
    {
        $this->requireCsrf('/banquets/create');

        try {
            $this->banquetService->create(Auth::id(), $request->allPost());
        } catch (ValidationException $exception) {
            $this->validationError($exception, '/banquets/create');
        } catch (DomainException $exception) {
            $this->businessError($exception, '/banquets/create');
        }

        \Apoki\Core\Flash::success('宴请已添加。');
        $this->response->redirect('/banquets');
    }

    public function edit(Request $request, array $params): string
    {
        $id = $this->idFromParams($params);
        return $this->form(Auth::id(), $id);
    }

    public function update(Request $request, array $params): never
    {
        $id = $this->idFromParams($params);
        $fallback = '/banquets/' . $id . '/edit';
        $this->requireCsrf($fallback);

        try {
            $this->banquetService->update(Auth::id(), $id, $request->allPost());
        } catch (NotFoundException $exception) {
            $this->notFoundResponse();
        } catch (ValidationException $exception) {
            $this->validationError($exception, $fallback);
        } catch (DomainException $exception) {
            $this->businessError($exception, $fallback);
        }

        \Apoki\Core\Flash::success('宴请已更新。');
        $this->response->redirect('/banquets');
    }

    public function destroy(Request $request, array $params): never
    {
        $id = $this->idFromParams($params);
        $this->requireCsrf('/banquets');

        try {
            $this->banquetService->delete(Auth::id(), $id);
        } catch (NotFoundException $exception) {
            $this->notFoundResponse();
        } catch (DomainException $exception) {
            $this->businessError($exception, '/banquets');
        }

        \Apoki\Core\Flash::success('宴请已删除。');
        $this->response->redirect('/banquets');
    }

    private function form(int $userId, ?int $id): string
    {
        if ($id !== null && $this->banquetService->find($userId, $id) === null) {
            return $this->response->view('errors/404', [
                'title' => '未找到',
                'active' => 'banquets',
            ], 404);
        }

        $data = $this->banquetService->formData($userId, $id);
        $data['title'] = $id === null ? '新增宴请' : '编辑宴请';
        $data['active'] = 'banquets';

        return $this->response->view('banquets/form', $data);
    }

    private function notFoundResponse(): never
    {
        echo $this->response->view('errors/404', [
            'title' => '未找到',
            'active' => 'banquets',
        ], 404);
        exit;
    }
}
