<form method="post" action="/login" class="d-grid gap-3">
    <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
    <input type="hidden" name="next" value="<?= $view->e($next ?? '/') ?>">
    <div>
        <label class="form-label" for="username">用户名</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input class="form-control" id="username" name="username" type="text" maxlength="30" value="<?= $view->e($view->old('username')) ?>" required autofocus>
        </div>
    </div>
    <div>
        <label class="form-label" for="password">密码</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input class="form-control" id="password" name="password" type="password" maxlength="72" required>
        </div>
    </div>
    <button class="btn btn-primary py-2" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i>登录</button>
    <div class="text-center text-muted small">
        还没有账号？<a href="/register">立即注册</a>
    </div>
</form>
