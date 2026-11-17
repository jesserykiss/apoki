<div class="page-heading">
    <h1>宴请</h1>
    <a class="btn btn-primary" href="/banquets/create"><i class="bi bi-plus-lg me-1"></i>新增宴请</a>
</div>

<form class="filter-bar" method="get" action="/banquets">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label" for="keyword">关键字</label>
            <input class="form-control" id="keyword" name="keyword" type="text" value="<?= $view->e($filters['keyword'] ?? '') ?>" placeholder="标题、地点或备注">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="type_id">类型</label>
            <select class="form-select" id="type_id" name="type_id">
                <option value="">全部</option>
                <?php foreach ($types ?? [] as $type): ?>
                    <option value="<?= (int) $type['id'] ?>"<?= $view->selected($filters['type_id'] ?? '', (string) $type['id']) ?>><?= $view->e($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="host_type">主办</label>
            <select class="form-select" id="host_type" name="host_type">
                <option value="">全部</option>
                <option value="1"<?= $view->selected($filters['host_type'] ?? '', '1') ?>>自己办</option>
                <option value="2"<?= $view->selected($filters['host_type'] ?? '', '2') ?>>亲戚办</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="date_from">开始日期</label>
            <input class="form-control" id="date_from" name="date_from" type="date" value="<?= $view->e($filters['date_from'] ?? '') ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="date_to">结束日期</label>
            <input class="form-control" id="date_to" name="date_to" type="date" value="<?= $view->e($filters['date_to'] ?? '') ?>">
        </div>
        <div class="col-12 d-flex gap-2 justify-content-end">
            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search me-1"></i>查询</button>
            <a class="btn btn-outline-secondary" href="/banquets" title="重置"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="table-panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>宴请</th>
                <th>类型</th>
                <th>主办</th>
                <th>主办亲戚</th>
                <th>日期</th>
                <th>地点</th>
                <th>备注</th>
                <th class="text-end">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="8" class="empty-state">暂无宴请记录</td></tr>
            <?php endif; ?>
            <?php foreach ($items ?? [] as $item): ?>
                <tr>
                    <td class="fw-semibold"><?= $view->e($item['title']) ?></td>
                    <td><?= $view->e($item['type_name']) ?></td>
                    <td><?= $view->e($view->hostType((string) $item['host_type'])) ?></td>
                    <td><?= $view->e($item['host_relative_name'] ?? '') ?></td>
                    <td><?= $view->e($item['banquet_date']) ?></td>
                    <td><?= $view->e($item['location'] ?? '') ?></td>
                    <td class="text-truncate" style="max-width:200px;"><?= $view->e($item['remark'] ?? '') ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="/banquets/<?= (int) $item['id'] ?>/edit" title="编辑"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="/banquets/<?= (int) $item['id'] ?>/delete" data-confirm="确定删除「<?= $view->e($item['title']) ?>」吗？">
                            <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="删除"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php $result = $result ?? []; $filters = $filters ?? []; $view->partial('partials/pagination', ['result' => $result, 'filters' => $filters]); ?>
</div>
