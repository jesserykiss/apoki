<div class="page-heading">
    <h1>修改密码</h1>
</div>

<div class="form-frame">
    <form method="post" action="/password" class="d-grid gap-3">
        <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
        <div>
            <label class="form-label" for="current_password">原密码</label>
            <input class="form-control" id="current_password" name="current_password" type="password" required autofocus>
        </div>
        <div>
            <label class="form-label" for="new_password">新密码</label>
            <input class="form-control" id="new_password" name="new_password" type="password" minlength="8" maxlength="72" required>
        </div>
        <div>
            <label class="form-label" for="confirm_password">确认新密码</label>
            <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="8" maxlength="72" required>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1"></i>保存</button>
            <a class="btn btn-outline-secondary" href="/">返回看板</a>
        </div>
    </form>
</div>
