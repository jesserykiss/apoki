<div class="page-heading">
    <h1>统计</h1>
    <a class="btn btn-outline-secondary" href="/statistics" title="重置"><i class="bi bi-arrow-counterclockwise"></i></a>
</div>

<form class="filter-bar" method="get" action="/statistics">
    <div class="row g-3 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label" for="date_from">开始日期</label>
            <input class="form-control" id="date_from" name="date_from" type="date" value="<?= $view->e($filters['date_from'] ?? '') ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label" for="date_to">结束日期</label>
            <input class="form-control" id="date_to" name="date_to" type="date" value="<?= $view->e($filters['date_to'] ?? '') ?>">
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
            <label class="form-label" for="type_id">宴请类型</label>
            <select class="form-select" id="type_id" name="type_id">
                <option value="">全部</option>
                <?php foreach ($types ?? [] as $type): ?>
                    <option value="<?= (int) $type['id'] ?>"<?= $view->selected($filters['type_id'] ?? '', (string) $type['id']) ?>><?= $view->e($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-outline-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i>统计</button>
            <a class="btn btn-outline-secondary" href="/statistics" title="重置"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">总收礼</div>
            <div class="metric-value received"><?= $view->e($view->money($summary['total_received'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">总送礼</div>
            <div class="metric-value sent"><?= $view->e($view->money($summary['total_sent'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">净额</div>
            <div class="metric-value <?= str_starts_with((string) ($summary['net'] ?? '0'), '-') ? 'net-negative' : 'net-positive' ?>"><?= $view->e($view->money($summary['net'] ?? 0)) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="metric-card">
            <div class="metric-label">流水笔数</div>
            <div class="metric-value"><?= (int) ($summary['record_count'] ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <h2 class="h5 mb-2">按亲戚</h2>
        <div class="table-panel">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>亲戚</th>
                        <th class="text-end">笔数</th>
                        <th class="text-end">收礼</th>
                        <th class="text-end">送礼</th>
                        <th class="text-end">净额</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($byRelative)): ?>
                        <tr><td colspan="5" class="empty-state">当前筛选下暂无数据</td></tr>
                    <?php endif; ?>
                    <?php foreach ($byRelative ?? [] as $item): ?>
                        <tr>
                            <td class="fw-semibold"><?= $view->e($item['name']) ?></td>
                            <td class="text-end"><?= (int) $item['record_count'] ?></td>
                            <td class="text-end amount-positive"><?= $view->e($view->money($item['total_received'])) ?></td>
                            <td class="text-end amount-negative"><?= $view->e($view->money($item['total_sent'])) ?></td>
                            <td class="text-end <?= str_starts_with((string) $item['net'], '-') ? 'amount-negative' : 'amount-positive' ?>"><?= $view->e($view->money($item['net'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <h2 class="h5 mb-2">按宴请类型</h2>
        <div class="table-panel">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>类型</th>
                        <th class="text-end">笔数</th>
                        <th class="text-end">收礼</th>
                        <th class="text-end">送礼</th>
                        <th class="text-end">净额</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($byType)): ?>
                        <tr><td colspan="5" class="empty-state">当前筛选下暂无数据</td></tr>
                    <?php endif; ?>
                    <?php foreach ($byType ?? [] as $item): ?>
                        <tr>
                            <td class="fw-semibold"><?= $view->e($item['name']) ?></td>
                            <td class="text-end"><?= (int) $item['record_count'] ?></td>
                            <td class="text-end amount-positive"><?= $view->e($view->money($item['total_received'])) ?></td>
                            <td class="text-end amount-negative"><?= $view->e($view->money($item['total_sent'])) ?></td>
                            <td class="text-end <?= str_starts_with((string) $item['net'], '-') ? 'amount-negative' : 'amount-positive' ?>"><?= $view->e($view->money($item['net'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
