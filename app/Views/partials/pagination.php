<?php
$pageCount = (int) ($result['total_pages'] ?? 1);
$currentPage = (int) ($result['page'] ?? 1);
$filters = $filters ?? [];
?>
<?php if ((int) ($result['total'] ?? 0) > (int) ($result['per_page'] ?? 20) || $currentPage > 1): ?>
    <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-3 border-top">
        <span class="text-muted small">共 <?= (int) ($result['total'] ?? 0) ?> 条</span>
        <nav aria-label="分页">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $view->e($view->qs(array_merge($filters, ['page' => $currentPage - 1]))) ?>">上一页</a>
                </li>
                <li class="page-item disabled">
                    <span class="page-link"><?= $currentPage ?> / <?= $pageCount ?></span>
                </li>
                <li class="page-item <?= $currentPage >= $pageCount ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $view->e($view->qs(array_merge($filters, ['page' => $currentPage + 1]))) ?>">下一页</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>
