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
            --apoki-bg: #f3f5f7;
            --apoki-green: #177a55;
            --apoki-red: #b33a3a;
        }

        body {
            min-height: 100vh;
            background: var(--apoki-bg);
            color: var(--apoki-ink);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Microsoft YaHei", sans-serif;
        }

        .app-header {
            background: #fff;
            border-bottom: 1px solid var(--apoki-line);
        }

        .brand-mark {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #18745d;
        }

        .app-main {
            min-height: calc(100vh - 57px);
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .page-heading h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        .metric-card {
            border: 1px solid var(--apoki-line);
            border-radius: 8px;
            background: #fff;
            padding: 18px;
            height: 100%;
        }

        .metric-label {
            color: var(--apoki-muted);
            font-size: .86rem;
        }

        .metric-value {
            margin-top: 8px;
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: 0;
        }

        .metric-value.received {
            color: var(--apoki-green);
        }

        .metric-value.sent {
            color: var(--apoki-red);
        }

        .metric-value.net-positive {
            color: var(--apoki-green);
        }

        .metric-value.net-negative {
            color: var(--apoki-red);
        }

        .tool-frame {
            border: 1px solid var(--apoki-line);
            border-radius: 8px;
            background: #fff;
        }

        .filter-bar {
            border: 1px solid var(--apoki-line);
            border-radius: 8px;
            background: #fff;
            padding: 16px;
            margin-bottom: 18px;
        }

        .table-panel {
            border: 1px solid var(--apoki-line);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .table-panel .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafb;
            color: #596573;
            font-size: .86rem;
            font-weight: 650;
            border-bottom-color: var(--apoki-line);
            white-space: nowrap;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .amount-positive {
            color: var(--apoki-green);
            font-weight: 650;
        }

        .amount-negative {
            color: var(--apoki-red);
            font-weight: 650;
        }

        .empty-state {
            padding: 42px 20px;
            color: var(--apoki-muted);
            text-align: center;
        }

        .form-frame {
            max-width: 860px;
            border: 1px solid var(--apoki-line);
            border-radius: 8px;
            background: #fff;
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #3f4c58;
        }

        .btn {
            border-radius: 6px;
        }

        .btn-sm,
        .btn-group-sm > .btn {
            border-radius: 5px;
        }

        .text-muted {
            color: var(--apoki-muted) !important;
        }

        @media (max-width: 767.98px) {
            .page-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-heading h1 {
                font-size: 1.25rem;
            }

            .metric-value {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
<header class="app-header sticky-top">
    <nav class="navbar navbar-expand-lg py-0">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2 py-2" href="/">
                <span class="brand-mark"><i class="bi bi-gift"></i></span>
                <span class="fw-semibold">礼金往来</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="展开导航">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $view->active((string) $active, 'dashboard') ?>" href="/"><i class="bi bi-grid-1x2 me-1"></i>看板</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view->active((string) $active, 'relatives') ?>" href="/relatives"><i class="bi bi-people me-1"></i>亲戚</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view->active((string) $active, 'banquets') ?>" href="/banquets"><i class="bi bi-calendar-event me-1"></i>宴请</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view->active((string) $active, 'gifts') ?>" href="/gifts"><i class="bi bi-receipt me-1"></i>礼金</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $view->active((string) $active, 'statistics') ?>" href="/statistics"><i class="bi bi-bar-chart me-1"></i>统计</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 py-2">
                    <span class="text-muted d-none d-sm-inline"><?= $view->e($currentUser['nickname'] ?? $currentUser['username'] ?? '') ?></span>
                    <a class="btn btn-sm btn-outline-secondary" href="/password" title="修改密码"><i class="bi bi-shield-lock"></i><span class="d-none d-lg-inline ms-1">改密</span></a>
                    <form method="post" action="/logout" class="m-0">
                        <input type="hidden" name="_token" value="<?= $view->e($view->csrfToken()) ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-box-arrow-right"></i><span class="d-none d-lg-inline ms-1">退出</span></button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
</header>

<main class="app-main">
    <div class="container-fluid px-3 px-lg-4 py-4">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $view->e($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $view->e($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="关闭"></button>
            </div>
        <?php endif; ?>
        <?= $content ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
</script>
</body>
</html>
