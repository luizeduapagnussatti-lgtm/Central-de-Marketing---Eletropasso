<?php
declare(strict_types=1);

/**
 * Health check HTTP para deploy e monitoramento.
 * GET /health.php — retorna JSON com status de DB, migrations, Node e pastas gravaveis.
 */
require_once __DIR__ . '/config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$checks = [];
$healthy = true;

$manifestPath = marketing_path('sql/MANIFEST.json');
$manifest = is_file($manifestPath)
    ? json_decode((string) file_get_contents($manifestPath), true)
    : null;

$checks['app'] = [
    'ok' => true,
    'env' => marketing_env('APP_ENV', 'development'),
    'version' => '1.0.0',
];

try {
    $pdo = marketing_pdo();
    $pdo->query('SELECT 1');
    $checks['database'] = ['ok' => true];
} catch (Throwable $e) {
    $healthy = false;
    $checks['database'] = ['ok' => false, 'error' => $e->getMessage()];
}

if (($checks['database']['ok'] ?? false) === true) {
    try {
        $pdo = marketing_pdo();
        $st = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'modelos_layout'
               AND COLUMN_NAME = 'config_visual'"
        );
        $hasConfigVisual = (int) $st->fetchColumn() > 0;

        $stModelos = $pdo->query("SELECT codigo FROM modelos_layout ORDER BY codigo ASC");
        $modelos = $stModelos->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $expected = is_array($manifest['migrations'] ?? null) ? count($manifest['migrations']) : 7;
        $migrationOk = $hasConfigVisual && in_array('modelo_03', $modelos, true);

        $checks['migrations'] = [
            'ok' => $migrationOk,
            'expected_files' => $expected,
            'modelos' => $modelos,
            'config_visual_column' => $hasConfigVisual,
        ];

        if (!$migrationOk) {
            $healthy = false;
        }
    } catch (Throwable $e) {
        $healthy = false;
        $checks['migrations'] = ['ok' => false, 'error' => $e->getMessage()];
    }
}

$nodeBin = marketing_env('NODE_BIN', 'node') ?? 'node';
$nodeCmd = str_contains($nodeBin, ' ') ? '"' . $nodeBin . '"' : $nodeBin;
$nodeVersion = @shell_exec($nodeCmd . ' --version 2>&1');
$checks['node'] = [
    'ok' => is_string($nodeVersion) && trim($nodeVersion) !== '',
    'bin' => $nodeBin,
    'version' => is_string($nodeVersion) ? trim($nodeVersion) : null,
];
if (!$checks['node']['ok']) {
    $healthy = false;
}

$writableDirs = [
    'temp',
    'encartes/gerados',
    'assets/produtos/originais',
    'assets/produtos/limpas',
    'storage/logs',
];
$dirResults = [];
foreach ($writableDirs as $dir) {
    $path = marketing_path($dir);
    if (!is_dir($path)) {
        @mkdir($path, 0775, true);
    }
    $writable = is_dir($path) && is_writable($path);
    $dirResults[$dir] = $writable;
    if (!$writable) {
        $healthy = false;
    }
}
$checks['writable_dirs'] = [
    'ok' => !in_array(false, $dirResults, true),
    'paths' => $dirResults,
];

$checks['manifest'] = [
    'ok' => is_array($manifest) && is_array($manifest['migrations'] ?? null),
    'path' => 'sql/MANIFEST.json',
    'count' => is_array($manifest['migrations'] ?? null) ? count($manifest['migrations']) : 0,
];

http_response_code($healthy ? 200 : 503);
echo json_encode([
    'success' => $healthy,
    'data' => [
        'status' => $healthy ? 'healthy' : 'degraded',
        'checks' => $checks,
        'timestamp' => gmdate('c'),
    ],
    'error' => $healthy ? null : 'Um ou mais checks falharam.',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
