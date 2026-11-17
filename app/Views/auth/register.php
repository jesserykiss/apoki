<form method="post" action="/register" class="d-grid gap-3">
    <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
    <div>
        <label class="form-label" for="username">用户名</label>
        <input class="form-control" id="username" name="username" type="text" maxlength="30" value="<?= $view->e($view->old('username')) ?>" required autofocus>
    </div>
    <div>
        <label class="form-label" for="password">密码</label>
        <input class="form-control" id="password" name="password" type="password" minlength="8" maxlength="72" required>
    </div>
    <div>
        <label class="form-label" for="nickname">昵称</label>
        <input class="form-control" id="nickname" name="nickname" type="text" maxlength="50" value="<?= $view->e($view->old('nickname')) ?>" required>
    </div>
    <div class="row g-3">
        <div class="col-sm-6">
            <label class="form-label" for="phone">电话</label>
            <input class="form-control" id="phone" name="phone" type="text" maxlength="30" value="<?= $view->e($view->old('phone')) ?>">
        </div>
        <div class="col-sm-6">
            <label class="form-label" for="email">邮箱</label>
            <input class="form-control" id="email" name="email" type="email" maxlength="190" value="<?= $view->e($view->old('email')) ?>">
        </div>
    </div>
    <button class="btn btn-primary py-2" type="submit"><i class="bi bi-person-plus me-1"></i>注册</button>
    <div class="text-center text-muted small">
        已有账号？<a href="/login">返回登录</a>
    </div>
</form>
