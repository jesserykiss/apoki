<div class="page-heading">
    <h1>看板</h1>
</div>

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
    <div class="col-12 col-xl-7">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 class="h5 mb-0">往来最活跃的亲戚</h2>
            <a class="btn btn-sm btn-outline-primary" href="/statistics"><i class="bi bi-bar-chart me-1"></i>查看统计</a>
        </div>
        <div class="table-panel">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>亲戚</th>
                        <th class="text-end">收礼</th>
                        <th class="text-end">送礼</th>
                        <th class="text-end">净额</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($topRelatives)): ?>
                        <tr><td colspan="4" class="empty-state">暂无流水数据</td></tr>
                    <?php endif; ?>
                    <?php foreach ($topRelatives ?? [] as $item): ?>
                        <tr>
                            <td class="fw-semibold"><?= $view->e($item['name']) ?></td>
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
    <div class="col-12 col-xl-5">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 class="h5 mb-0">最近流水</h2>
            <a class="btn btn-sm btn-outline-primary" href="/gifts"><i class="bi bi-receipt me-1"></i>全部明细</a>
        </div>
        <div class="table-panel">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>日期</th>
                        <th>亲戚</th>
                        <th>方向</th>
                        <th class="text-end">金额</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentGifts)): ?>
                        <tr><td colspan="4" class="empty-state">暂无流水数据</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentGifts ?? [] as $item): ?>
                        <tr>
                            <td><?= $view->e($item['gift_date']) ?></td>
                            <td class="fw-semibold"><?= $view->e($item['relative_name']) ?></td>
                            <td>
                                <span class="badge <?= $item['direction'] === '1' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $view->e($view->direction((string) $item['direction'])) ?></span>
                            </td>
                            <td class="text-end <?= $item['direction'] === '1' ? 'amount-positive' : 'amount-negative' ?>"><?= $view->e($view->money($item['amount'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
