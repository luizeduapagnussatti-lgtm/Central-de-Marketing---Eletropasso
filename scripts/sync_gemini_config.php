<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$apiKey = marketing_env('GEMINI_API_KEY', '') ?? '';
$model = marketing_env('GEMINI_MODEL', 'gemini-2.5-flash') ?? 'gemini-2.5-flash';
$rembgBin = marketing_env('REMBG_BIN', 'rembg') ?? 'rembg';

if ($apiKey === '') {
    echo "AVISO: GEMINI_API_KEY vazia no .env\n";
}

marketing_config_set('gemini_api_key', $apiKey);
marketing_config_set('gemini_model', $model);
marketing_config_set('rembg_bin', $rembgBin);

$st = marketing_pdo()->query(
    "SELECT chave, IF(valor = '' OR valor IS NULL, '(vazio)', '(preenchido)') AS status
     FROM config_sistema WHERE chave IN ('gemini_api_key','gemini_model','rembg_bin')"
);

foreach ($st->fetchAll() as $row) {
    echo $row['chave'] . ': ' . $row['status'] . "\n";
}

echo "Sincronizacao OK\n";
