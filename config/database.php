<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

/** @var PDO|null */
$GLOBALS['marketing_pdo'] = null;

function marketing_pdo(): PDO
{
    if ($GLOBALS['marketing_pdo'] instanceof PDO) {
        return $GLOBALS['marketing_pdo'];
    }

    $host = marketing_env('MARKETING_DB_HOST', '127.0.0.1');
    $port = marketing_env('MARKETING_DB_PORT', '3306');
    $name = marketing_env('MARKETING_DB_NAME', 'central_marketing_eletropasso');
    $user = marketing_env('MARKETING_DB_USER', 'root');
    $pass = marketing_env('MARKETING_DB_PASS', '');
    $charset = marketing_env('MARKETING_DB_CHARSET', 'utf8mb4');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $GLOBALS['marketing_pdo'] = $pdo;

    return $pdo;
}

function marketing_config(string $chave, ?string $default = null): ?string
{
    static $cache = [];

    if (array_key_exists($chave, $cache)) {
        return $cache[$chave] ?? $default;
    }

    try {
        $st = marketing_pdo()->prepare('SELECT valor FROM config_sistema WHERE chave = ? LIMIT 1');
        $st->execute([$chave]);
        $row = $st->fetch();
        $cache[$chave] = $row !== false ? ($row['valor'] ?? $default) : $default;
    } catch (Throwable) {
        $cache[$chave] = $default;
    }

    return $cache[$chave] ?? $default;
}

function marketing_config_set(string $chave, string $valor): void
{
    $st = marketing_pdo()->prepare(
        'INSERT INTO config_sistema (chave, valor) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valor = VALUES(valor), updated_at = CURRENT_TIMESTAMP'
    );
    $st->execute([$chave, $valor]);
}
