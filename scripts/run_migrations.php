<?php
declare(strict_types=1);

/**
 * Aplica migrations SQL na ordem do manifesto sql/MANIFEST.json.
 *
 * Uso: php scripts/run_migrations.php
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

$manifestPath = marketing_path('sql/MANIFEST.json');
if (!is_file($manifestPath)) {
    fwrite(STDERR, "Manifesto nao encontrado: sql/MANIFEST.json\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestPath), true);
$migrations = $manifest['migrations'] ?? null;
if (!is_array($migrations) || $migrations === []) {
    fwrite(STDERR, "Manifesto invalido ou vazio.\n");
    exit(1);
}

$pdo = marketing_pdo();

foreach ($migrations as $migration) {
    $id = (string) ($migration['id'] ?? '');
    $file = (string) ($migration['file'] ?? '');
    $desc = (string) ($migration['description'] ?? '');

    if ($id === '' || $file === '') {
        fwrite(STDERR, "Migration invalida no manifesto.\n");
        exit(1);
    }

    $path = marketing_path($file);
    if (!is_file($path)) {
        fwrite(STDERR, "Arquivo ausente [{$id}]: {$file}\n");
        exit(1);
    }

    $sql = file_get_contents($path);
    if ($sql === false || trim($sql) === '') {
        fwrite(STDERR, "Arquivo vazio [{$id}]: {$file}\n");
        exit(1);
    }

    echo "Aplicando {$id} — {$desc}\n";
    try {
        $pdo->exec($sql);
    } catch (Throwable $e) {
        fwrite(STDERR, "Falha em {$id}: " . $e->getMessage() . "\n");
        exit(1);
    }
}

echo "Migrations concluidas (" . count($migrations) . " arquivos).\n";
