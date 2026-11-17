<div class="form-frame mx-auto text-center">
    <div class="display-6 mb-3"><i class="bi bi-exclamation-triangle text-muted"></i></div>
    <h1 class="h4 mb-2">系统暂时不可用</h1>
    <p class="text-muted mb-4">请稍后再试。</p>
    <?php if (!empty($message)): ?>
        <pre class="text-start small border p-3 overflow-auto"><?= $view->e($message) ?><?= !empty($trace) ? PHP_EOL . $view->e($trace) : '' ?></pre>
    <?php endif; ?>
    <a class="btn btn-primary" href="/"><i class="bi bi-house me-1"></i>返回看板</a>
</div>
