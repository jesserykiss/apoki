<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

$config = require dirname(__DIR__) . '/config/config.php';

echo 'PHP_VERSION=' . PHP_VERSION . PHP_EOL;
echo 'PHP_8_1_OR_NEWER=' . (version_compare(PHP_VERSION, '8.1.0', '>=') ? 'OK' : 'FAIL') . PHP_EOL;

$requiredExtensions = ['pdo', 'pdo_mysql', 'bcmath', 'session'];
foreach ($requiredExtensions as $extension) {
    echo 'EXT_' . strtoupper($extension) . '=' . (extension_loaded($extension) ? 'OK' : 'MISSING') . PHP_EOL;
}

try {
    $db = $config['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        $db['port'],
        $db['database'],
        $db['charset']
    );
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $tables = ['users', 'relatives', 'banquet_types', 'banquets', 'gift_records'];
    foreach ($tables as $table) {
        $exists = (int) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = " .
            $pdo->quote($table)
        )->fetchColumn();
        echo 'TABLE_' . strtoupper($table) . '=' . ($exists === 1 ? 'OK' : 'MISSING') . PHP_EOL;
    }

    $types = (int) $pdo->query(
        'SELECT COUNT(*) FROM banquet_types WHERE is_system = 1 AND user_id IS NULL'
    )->fetchColumn();
    echo 'SYSTEM_BANQUET_TYPES=' . $types . PHP_EOL;
    echo 'DATABASE_CONNECTION=OK' . PHP_EOL;
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'DATABASE_CONNECTION=ERROR' . PHP_EOL;
    echo 'ERROR=' . $exception->getMessage() . PHP_EOL;
}
