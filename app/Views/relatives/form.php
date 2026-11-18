<?php
$current = $relative;
$isEdit = !empty($current);
$action = $isEdit ? '/relatives/' . (int) $current['id'] : '/relatives';
$name = $view->old('name', $current['name'] ?? '');
$relation = $view->old('relation', $current['relation'] ?? '');
$familySide = $view->old('family_side', $current['family_side'] ?? 'other');
$category = $view->old('category', $current['category'] ?? 'other');
$gender = $view->old('gender', $current['gender'] ?? 'unknown');
$phone = $view->old('phone', $current['phone'] ?? '');
$remark = $view->old('remark', $current['remark'] ?? '');
?>
<div class="page-heading">
    <h1><?= $view->e($title) ?></h1>
    <a class="btn btn-outline-secondary" href="/relatives"><i class="bi bi-arrow-left me-1"></i>返回列表</a>
</div>

<div class="form-frame">
    <form method="post" action="<?= $view->e($action) ?>" class="d-grid gap-3">
        <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="name">姓名</label>
                <input class="form-control" id="name" name="name" type="text" maxlength="50" value="<?= $view->e($name) ?>" required autofocus>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="relation">关系</label>
                <input class="form-control" id="relation" name="relation" type="text" maxlength="30" value="<?= $view->e($relation) ?>" placeholder="如：舅舅、表妹" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="family_side">家族</label>
                <select class="form-select" id="family_side" name="family_side">
                    <option value="father"<?= $view->selected($familySide, 'father') ?>>父方</option>
                    <option value="mother"<?= $view->selected($familySide, 'mother') ?>>母方</option>
                    <option value="spouse"<?= $view->selected($familySide, 'spouse') ?>>配偶方</option>
					<option value="brother"<?= $view->selected($familySide, 'brother') ?>>兄弟姐妹</option>
                    <option value="other"<?= $view->selected($familySide, 'other') ?>>朋友</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="category">辈分</label>
                <select class="form-select" id="category" name="category">
                    <option value="elder"<?= $view->selected($category, 'elder') ?>>长辈</option>
                    <option value="peer"<?= $view->selected($category, 'peer') ?>>平辈</option>
                    <option value="younger"<?= $view->selected($category, 'younger') ?>>晚辈</option>
                    <option value="other"<?= $view->selected($category, 'other') ?>>其他</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="gender">性别</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="unknown"<?= $view->selected($gender, 'unknown') ?>>未填</option>
                    <option value="male"<?= $view->selected($gender, 'male') ?>>男</option>
                    <option value="female"<?= $view->selected($gender, 'female') ?>>女</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="phone">电话</label>
                <input class="form-control" id="phone" name="phone" type="text" maxlength="30" value="<?= $view->e($phone) ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="remark">备注</label>
                <textarea class="form-control" id="remark" name="remark" rows="3" maxlength="500"><?= $view->e($remark) ?></textarea>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>保存</button>
            <a class="btn btn-outline-secondary" href="/relatives">取消</a>
        </div>
    </form>
</div>
