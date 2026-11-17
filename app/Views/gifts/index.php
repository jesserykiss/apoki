<div class="page-heading">
    <h1>礼金明细</h1>
    <a class="btn btn-primary" href="/gifts/create"><i class="bi bi-plus-lg me-1"></i>新增礼金</a>
</div>

<form class="filter-bar" method="get" action="/gifts">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label" for="keyword">关键字</label>
            <input class="form-control" id="keyword" name="keyword" type="text" value="<?= $view->e($filters['keyword'] ?? '') ?>" placeholder="礼品、备注、亲戚或宴请">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="direction">方向</label>
            <select class="form-select" id="direction" name="direction">
                <option value="">全部</option>
                <option value="1"<?= $view->selected($filters['direction'] ?? '', '1') ?>>收礼</option>
                <option value="2"<?= $view->selected($filters['direction'] ?? '', '2') ?>>送礼</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="relative_id">亲戚</label>
            <select class="form-select" id="relative_id" name="relative_id">
                <option value="">全部</option>
                <?php foreach ($relatives ?? [] as $relative): ?>
                    <option value="<?= (int) $relative['id'] ?>"<?= $view->selected($filters['relative_id'] ?? '', (string) $relative['id']) ?>><?= $view->e($relative['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="banquet_id">宴请</label>
            <select class="form-select" id="banquet_id" name="banquet_id">
                <option value="">全部</option>
                <?php foreach ($banquets ?? [] as $banquet): ?>
                    <option value="<?= (int) $banquet['id'] ?>"<?= $view->selected($filters['banquet_id'] ?? '', (string) $banquet['id']) ?>><?= $view->e($banquet['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="gift_type">礼金形式</label>
            <select class="form-select" id="gift_type" name="gift_type">
                <option value="">全部</option>
                <option value="1"<?= $view->selected($filters['gift_type'] ?? '', '1') ?>>现金</option>
                <option value="2"<?= $view->selected($filters['gift_type'] ?? '', '2') ?>>转账</option>
                <option value="3"<?= $view->selected($filters['gift_type'] ?? '', '3') ?>>礼品</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="type_id">宴请类型</label>
            <select class="form-select" id="type_id" name="type_id">
                <option value="">全部</option>
                <?php foreach ($types ?? [] as $type): ?>
                    <option value="<?= (int) $type['id'] ?>"<?= $view->selected($filters['type_id'] ?? '', (string) $type['id']) ?>><?= $view->e($type['name']) ?></option>
                <?php endforeach; ?>
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
            <a class="btn btn-outline-secondary" href="/gifts" title="重置"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="table-panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>日期</th>
                <th>亲戚</th>
                <th>宴请</th>
                <th>方向</th>
                <th>金额</th>
                <th>形式</th>
                <th>说明</th>
                <th>备注</th>
                <th class="text-end">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="9" class="empty-state">暂无礼金记录</td></tr>
            <?php endif; ?>
            <?php foreach ($items ?? [] as $item): ?>
                <tr>
                    <td><?= $view->e($item['gift_date']) ?></td>
                    <td class="fw-semibold"><?= $view->e($item['relative_name']) ?></td>
                    <td><?= $view->e($item['banquet_title']) ?></td>
                    <td>
                        <span class="badge <?= $item['direction'] === '1' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $view->e($view->direction((string) $item['direction'])) ?></span>
                    </td>
                    <td class="<?= $item['direction'] === '1' ? 'amount-positive' : 'amount-negative' ?>"><?= $view->e($view->money($item['amount'])) ?></td>
                    <td><?= $view->e($view->giftType((string) $item['gift_type'])) ?></td>
                    <td class="text-truncate" style="max-width:170px;"><?= $view->e($item['gift_desc'] ?? '') ?></td>
                    <td class="text-truncate" style="max-width:170px;"><?= $view->e($item['remark'] ?? '') ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="/gifts/<?= (int) $item['id'] ?>/edit" title="编辑"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="/gifts/<?= (int) $item['id'] ?>/delete" data-confirm="确定删除这笔礼金记录吗？">
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
