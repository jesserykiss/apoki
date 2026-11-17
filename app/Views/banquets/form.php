<?php
$current = $banquet ?? null;
$isEdit = !empty($current);
$action = $isEdit ? '/banquets/' . (int) $current['id'] : '/banquets';
$hostType = (string) $view->old('host_type', $current['host_type'] ?? '1');
$hostRelativeId = (string) $view->old('host_relative_id', $current['host_relative_id'] ?? '');
$lockHost = !empty($has_gifts);
?>
<div class="page-heading">
    <h1><?= $view->e($title) ?></h1>
    <a class="btn btn-outline-secondary" href="/banquets"><i class="bi bi-arrow-left me-1"></i>返回列表</a>
</div>

<div class="form-frame">
    <form method="post" action="<?= $view->e($action) ?>" class="d-grid gap-3">
        <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label class="form-label" for="title">宴请名称</label>
                <input class="form-control" id="title" name="title" type="text" maxlength="100" value="<?= $view->e($view->old('title', $current['title'] ?? '')) ?>" required autofocus>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="type_id">宴请类型</label>
                <select class="form-select" id="type_id" name="type_id" required>
                    <?php foreach ($types ?? [] as $type): ?>
                        <option value="<?= (int) $type['id'] ?>"<?= $view->selected($view->old('type_id', $current['type_id'] ?? ''), (string) $type['id']) ?>><?= $view->e($type['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="host_type">主办类型</label>
                <select class="form-select" id="host_type" name="host_type"<?= $lockHost ? ' disabled' : '' ?>>
                    <option value="1"<?= $view->selected($hostType, '1') ?>>自己办</option>
                    <option value="2"<?= $view->selected($hostType, '2') ?>>亲戚办</option>
                </select>
                <?php if ($lockHost): ?>
                    <input type="hidden" name="host_type" value="<?= $view->e($hostType) ?>">
                <?php endif; ?>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="host_relative_id">主办亲戚</label>
                <select class="form-select" id="host_relative_id" name="host_relative_id"<?= $lockHost ? ' disabled' : '' ?>>
                    <option value="">请选择</option>
                    <?php foreach ($relatives ?? [] as $relative): ?>
                        <option value="<?= (int) $relative['id'] ?>"<?= $view->selected($hostRelativeId, (string) $relative['id']) ?>><?= $view->e($relative['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($lockHost): ?>
                    <input type="hidden" name="host_relative_id" value="<?= $view->e($hostRelativeId) ?>">
                <?php endif; ?>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="banquet_date">日期</label>
                <input class="form-control" id="banquet_date" name="banquet_date" type="date" value="<?= $view->e($view->old('banquet_date', $current['banquet_date'] ?? '')) ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="location">地点</label>
                <input class="form-control" id="location" name="location" type="text" maxlength="200" value="<?= $view->e($view->old('location', $current['location'] ?? '')) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="remark">备注</label>
                <textarea class="form-control" id="remark" name="remark" rows="3" maxlength="500"><?= $view->e($view->old('remark', $current['remark'] ?? '')) ?></textarea>
            </div>
        </div>
        <?php if ($lockHost): ?>
            <div class="text-muted small">已有礼金流水，主办类型和主办亲戚不可修改。</div>
        <?php endif; ?>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>保存</button>
            <a class="btn btn-outline-secondary" href="/banquets">取消</a>
        </div>
    </form>
</div>

<script>
    (function () {
        var type = document.getElementById('host_type');
        var relative = document.getElementById('host_relative_id');
        var lock = <?= $lockHost ? 'true' : 'false' ?>;

        function sync() {
            if (!type || !relative) return;
            var isSelf = type.value === '1';
            relative.disabled = lock || isSelf;
            relative.required = !isSelf;
            if (isSelf) relative.value = '';
        }

        if (type) type.addEventListener('change', sync);
        sync();
    })();
</script>
