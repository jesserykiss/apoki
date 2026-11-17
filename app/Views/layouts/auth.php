<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $view->e($title ?? '礼金往来') ?> · 礼金往来</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --apoki-ink: #243342;
            --apoki-muted: #6c7885;
            --apoki-line: #dfe5ea;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: #eef2f4;
            color: var(--apoki-ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Microsoft YaHei", sans-serif;
        }

        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
        }

        .auth-panel {
            width: 100%;
            max-width: 440px;
            border: 1px solid var(--apoki-line);
            border-radius: 8px;
            background: #fff;
            padding: 28px;
            box-shadow: 0 12px 36px rgb(36 51 66 / 8%);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #18745d;
        }

        .auth-panel h1 {
            margin-bottom: 22px;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        .btn {
            border-radius: 6px;
        }
    </style>
</head>
<body>
<main class="auth-wrap">
    <section class="auth-panel">
        <div class="brand">
            <span class="brand-mark"><i class="bi bi-gift"></i></span>
            <strong>礼金往来</strong>
        </div>
        <h1><?= $view->e($title ?? '') ?></h1>
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><?= $view->e($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger"><?= $view->e($flashError) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
