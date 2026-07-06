<?php
declare(strict_types=1);

/**
 * Testa Rembg CLI com uma imagem existente ou placeholder.
 * Uso: php scripts/test_rembg.php [caminho_imagem]
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

$input = $argv[1] ?? null;

if ($input === null) {
    $candidates = glob(marketing_path('assets/produtos/originais/*.{jpg,jpeg,png,webp}'), GLOB_BRACE);
    $input = $candidates[0] ?? null;
}

if ($input === null || !is_file($input)) {
    echo "ERRO: Informe uma imagem existente.\n";
    echo "Uso: php scripts/test_rembg.php caminho/foto.jpg\n";
    exit(1);
}

$output = marketing_path('assets/produtos/limpas/test_rembg_' . time() . '.png');
$assistant = new MarketingAssistant();

echo "Entrada: {$input}\n";
echo "Saida:   {$output}\n";

if ($assistant->removerFundoImagem($input, $output)['ok']) {
    echo "Rembg OK — arquivo gerado.\n";
    exit(0);
}

echo "Rembg FALHOU — verifique REMBG_BIN e storage/logs/\n";
exit(1);
