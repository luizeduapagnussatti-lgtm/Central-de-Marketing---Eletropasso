<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$codigo = $argv[1] ?? 'modelo_01';
$formato = $argv[2] ?? '9x16';
$outputRelativo = 'assets/modelos/' . $codigo . '.png';
$outputPath = marketing_path($outputRelativo);

echo "Gerando preview do modelo {$codigo} ({$formato})...\n";

try {
    $render = new EncarteRenderService();
    $render->gerarPreviewModelo($codigo, $formato, $outputPath);
    echo "Preview salvo em: {$outputRelativo}\n";
    echo "OK\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
