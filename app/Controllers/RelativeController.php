<?php

declare(strict_types=1);

namespace Apoki\Controllers;

use Apoki\Core\Auth;
use Apoki\Core\NotFoundException;
use Apoki\Core\Request;
use Apoki\Core\Response;
use Apoki\Core\ValidationException;
use Apoki\Services\RelativeService;
use DomainException;

final class RelativeController extends Controller
{
    public function __construct(
        Request $request,
        Response $response,
        private readonly RelativeService $relativeService
    ) {
        parent::__construct($request, $response);
    }

    public function index(Request $request, array $params): string
    {
        $filters = [
            'keyword' => trim((string) $request->query('keyword')),
            'family_side' => (string) $request->query('family_side'),
            'category' => (string) $request->query('category'),
        ];
        $page = max(1, $request->int('page', 1));
        $result = $this->relativeService->paginate(Auth::id(), $filters, $page);
        $result['filters'] = $filters;
        $result['title'] = '亲戚';
        $result['active'] = 'relatives';

        return $this->response->view('relatives/index', $result);
    }

    public function create(Request $request, array $params): string
    {
        return $this->response->view('relatives/form', [
            'title' => '新增亲戚',
            'active' => 'relatives',
            'relative' => null,
        ]);
    }

    public function store(Request $request, array $params): never
    {
        $this->requireCsrf('/relatives/create');

        try {
            $this->relativeService->create(Auth::id(), $request->allPost());
        } catch (ValidationException $exception) {
            $this->validationError($exception, '/relatives/create');
        } catch (DomainException $exception) {
            $this->businessError($exception, '/relatives/create');
        }

        \Apoki\Core\Flash::success('亲戚已添加。');
        $this->response->redirect('/relatives');
    }

    public function edit(Request $request, array $params): string
    {
        $relative = $this->relativeService->find(Auth::id(), $this->idFromParams($params));
        if ($relative === null) {
            return $this->response->view('errors/404', [
                'title' => '未找到',
                'active' => 'relatives',
            ], 404);
        }

        return $this->response->view('relatives/form', [
            'title' => '编辑亲戚',
            'active' => 'relatives',
            'relative' => $relative,
        ]);
    }

    public function update(Request $request, array $params): never
    {
        $id = $this->idFromParams($params);
        $fallback = '/relatives/' . $id . '/edit';
        $this->requireCsrf($fallback);

        try {
            $this->relativeService->update(Auth::id(), $id, $request->allPost());
        } catch (NotFoundException $exception) {
            $this->notFoundResponse();
        } catch (ValidationException $exception) {
            $this->validationError($exception, $fallback);
        } catch (DomainException $exception) {
            $this->businessError($exception, $fallback);
        }

        \Apoki\Core\Flash::success('亲戚已更新。');
        $this->response->redirect('/relatives');
    }

    public function destroy(Request $request, array $params): never
    {
        $id = $this->idFromParams($params);
        $this->requireCsrf('/relatives');

        try {
            $this->relativeService->delete(Auth::id(), $id);
        } catch (NotFoundException $exception) {
            $this->notFoundResponse();
        } catch (DomainException $exception) {
            $this->businessError($exception, '/relatives');
        }

        \Apoki\Core\Flash::success('亲戚已删除。');
        $this->response->redirect('/relatives');
    }

    private function notFoundResponse(): never
    {
        echo $this->response->view('errors/404', [
            'title' => '未找到',
            'active' => 'relatives',
        ], 404);
        exit;
    }
}
