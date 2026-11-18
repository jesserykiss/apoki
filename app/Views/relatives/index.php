<div class="page-heading">
    <h1>亲戚</h1>
    <a class="btn btn-primary" href="/relatives/create"><i class="bi bi-plus-lg me-1"></i>新增亲戚</a>
</div>

<form class="filter-bar" method="get" action="/relatives">
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label" for="keyword">姓名或关系</label>
            <input class="form-control" id="keyword" name="keyword" type="text" value="<?= $view->e($filters['keyword'] ?? '') ?>" placeholder="输入姓名、关系或备注">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="family_side">家族</label>
            <select class="form-select" id="family_side" name="family_side">
                <option value="">全部</option>
                <option value="father"<?= $view->selected($filters['family_side'] ?? '', 'father') ?>>父方</option>
                <option value="mother"<?= $view->selected($filters['family_side'] ?? '', 'mother') ?>>母方</option>
                <option value="spouse"<?= $view->selected($filters['family_side'] ?? '', 'spouse') ?>>配偶方</option>
				<option value="brother"<?= $view->selected($filters['family_side'] ?? '', 'brother') ?>>兄弟姐妹</option>
                <option value="other"<?= $view->selected($filters['family_side'] ?? '', 'other') ?>>朋友</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="category">辈分</label>
            <select class="form-select" id="category" name="category">
                <option value="">全部</option>
                <option value="elder"<?= $view->selected($filters['category'] ?? '', 'elder') ?>>长辈</option>
                <option value="peer"<?= $view->selected($filters['category'] ?? '', 'peer') ?>>平辈</option>
                <option value="younger"<?= $view->selected($filters['category'] ?? '', 'younger') ?>>晚辈</option>
                <option value="other"<?= $view->selected($filters['category'] ?? '', 'other') ?>>其他</option>
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button class="btn btn-outline-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i>查询</button>
            <a class="btn btn-outline-secondary" href="/relatives" title="重置"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="table-panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th>姓名</th>
                <th>关系</th>
                <th>家族</th>
                <th>辈分</th>
                <th>性别</th>
                <th>电话</th>
                <th>备注</th>
                <th class="text-end">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="8" class="empty-state">暂无亲戚记录</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($items ?? [] as $item): ?>
                <tr>
                    <td class="fw-semibold"><?= $view->e($item['name']) ?></td>
                    <td><?= $view->e($item['relation']) ?></td>
                    <td><?= $view->e($view->familySide((string) $item['family_side'])) ?></td>
                    <td><?= $view->e($view->category((string) $item['category'])) ?></td>
                    <td><?= $view->e($view->gender((string) $item['gender'])) ?></td>
                    <td><?= $view->e($item['phone'] ?? '') ?></td>
                    <td class="text-truncate" style="max-width:220px;"><?= $view->e($item['remark'] ?? '') ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="/relatives/<?= (int) $item['id'] ?>/edit" title="编辑"><i class="bi bi-pencil"></i></a>
                        <form class="d-inline" method="post" action="/relatives/<?= (int) $item['id'] ?>/delete" data-confirm="确定删除 <?= $view->e($item['name']) ?> 吗？">
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
