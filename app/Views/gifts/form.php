<?php
$current = $gift ?? null;
$isEdit = !empty($current);
$action = $isEdit ? '/gifts/' . (int) $current['id'] : '/gifts';
$banquetId = (string) $view->old('banquet_id', $current['banquet_id'] ?? $preselected_banquet_id ?? '');
$relativeId = (string) $view->old('relative_id', $current['relative_id'] ?? '');
$direction = (string) $view->old('direction', $current['direction'] ?? '1');
$giftType = (string) $view->old('gift_type', $current['gift_type'] ?? '1');
?>
<div class="page-heading">
    <h1><?= $view->e($title) ?></h1>
    <a class="btn btn-outline-secondary" href="/gifts"><i class="bi bi-arrow-left me-1"></i>返回列表</a>
</div>

<div class="form-frame">
    <form method="post" action="<?= $view->e($action) ?>" class="d-grid gap-3">
        <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
        <input type="hidden" name="direction" id="direction" value="<?= $view->e($direction) ?>">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="banquet_id">宴请</label>
                <select class="form-select" id="banquet_id" name="banquet_id" required>
                    <option value="">请选择</option>
                    <?php foreach ($banquets ?? [] as $banquet): ?>
                        <option
                            value="<?= (int) $banquet['id'] ?>"
                            data-host-type="<?= (int) $banquet['host_type'] ?>"
                            data-relative-id="<?= (int) $banquet['host_relative_id'] ?>"
                            <?= $view->selected($banquetId, (string) $banquet['id']) ?>
                        ><?= $view->e($banquet['title']) ?> · <?= $view->e($banquet['banquet_date']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="relative_id">亲戚</label>
                <select class="form-select" id="relative_id" name="relative_id" required>
                    <option value="">请选择</option>
                    <?php foreach ($relatives ?? [] as $relative): ?>
                        <option value="<?= (int) $relative['id'] ?>"<?= $view->selected($relativeId, (string) $relative['id']) ?>><?= $view->e($relative['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">方向</label>
                <div class="form-control d-flex align-items-center gap-2">
                    <span class="badge text-bg-success" id="direction-label"><?= $view->e($view->direction($direction)) ?></span>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="amount">金额</label>
                <div class="input-group">
                    <span class="input-group-text">¥</span>
                    <input class="form-control" id="amount" name="amount" type="text" inputmode="decimal" maxlength="11" value="<?= $view->e($view->old('amount', $current['amount'] ?? '')) ?>" required>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="gift_type">礼金形式</label>
                <select class="form-select" id="gift_type" name="gift_type">
                    <option value="1"<?= $view->selected($giftType, '1') ?>>现金</option>
                    <option value="2"<?= $view->selected($giftType, '2') ?>>转账</option>
                    <option value="3"<?= $view->selected($giftType, '3') ?>>礼品</option>
                </select>
            </div>
            <div class="col-12" id="gift_desc_group">
                <label class="form-label" for="gift_desc">礼品说明</label>
                <input class="form-control" id="gift_desc" name="gift_desc" type="text" maxlength="200" value="<?= $view->e($view->old('gift_desc', $current['gift_desc'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="gift_date">流水日期</label>
                <input class="form-control" id="gift_date" name="gift_date" type="date" value="<?= $view->e($view->old('gift_date', $current['gift_date'] ?? '')) ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="remark">备注</label>
                <textarea class="form-control" id="remark" name="remark" rows="3" maxlength="500"><?= $view->e($view->old('remark', $current['remark'] ?? '')) ?></textarea>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>保存</button>
            <a class="btn btn-outline-secondary" href="/gifts">取消</a>
        </div>
    </form>
</div>

<script>
    (function () {
        var banquet = document.getElementById('banquet_id');
        var relatives = document.getElementById('relative_id');
        var direction = document.getElementById('direction');
        var directionLabel = document.getElementById('direction-label');
        var giftType = document.getElementById('gift_type');
        var descGroup = document.getElementById('gift_desc_group');

        function syncBanquet() {
            var selected = banquet.options[banquet.selectedIndex];
            var hostType = selected ? selected.dataset.hostType : '1';
            var hostRelativeId = selected ? selected.dataset.relativeId : '0';
            var isRelativeHost = hostType === '2';

            direction.value = isRelativeHost ? '2' : '1';
            directionLabel.textContent = isRelativeHost ? '送礼' : '收礼';
            directionLabel.className = isRelativeHost ? 'badge text-bg-secondary' : 'badge text-bg-success';

            for (var index = 0; index < relatives.options.length; index++) {
                var option = relatives.options[index];
                if (!option.value) continue;
                option.hidden = isRelativeHost && option.value !== hostRelativeId;
                option.disabled = option.hidden;
                if (isRelativeHost && option.value === hostRelativeId) {
                    relatives.value = option.value;
                }
            }
            if (!isRelativeHost && relatives.options[relatives.selectedIndex].hidden) {
                relatives.value = '';
            }
        }

        function syncGiftType() {
            var isGift = giftType.value === '3';
            descGroup.style.display = isGift ? '' : 'none';
            document.getElementById('gift_desc').required = isGift;
        }

        banquet.addEventListener('change', syncBanquet);
        giftType.addEventListener('change', syncGiftType);
        syncBanquet();
        syncGiftType();
    })();
</script>
