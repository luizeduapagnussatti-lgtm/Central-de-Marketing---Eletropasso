<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

$pdo = marketing_pdo();
$st = $pdo->query("SELECT chave, valor FROM config_sistema WHERE chave LIKE 'rembg%' ORDER BY chave");
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

echo "=== config_sistema (rembg*) ===\n";
if (!$rows) {
    echo "(vazio)\n";
}
foreach ($rows as $r) {
    echo $r['chave'] . ' = ' . $r['valor'] . "\n";
}

echo "\n=== .env REMBG_* ===\n";
foreach (['REMBG_BIN', 'REMBG_MODEL', 'REMBG_ALPHA_MATTING', 'REMBG_POST_PROCESS_MASK', 'REMBG_WHITE_REFINE', 'REMBG_MAX_EDGE'] as $k) {
    echo $k . ' = ' . (marketing_env($k, '(nao definido)') ?? '(null)') . "\n";
}

echo "\n=== MarketingAssistant (valores efetivos em runtime) ===\n";
$a = new MarketingAssistant();
$ref = new ReflectionClass($a);
foreach (['rembgBin', 'rembgModel', 'rembgAlphaMatting', 'rembgPostProcessMask', 'rembgWhiteRefine', 'rembgMaxEdge'] as $p) {
    $prop = $ref->getProperty($p);
    $prop->setAccessible(true);
    $val = $prop->getValue($a);
    if (is_bool($val)) {
        $val = $val ? 'true' : 'false';
    }
    echo $p . ' = ' . $val . "\n";
}

$lento = false;
$model = (string) $ref->getProperty('rembgModel')->getValue($a);
$alpha = (bool) $ref->getProperty('rembgAlphaMatting')->getValue($a);
$ppm = (bool) $ref->getProperty('rembgPostProcessMask')->getValue($a);

echo "\n=== Diagnostico ===\n";
if (in_array($model, ['birefnet-general', 'bria-rmbg'], true)) {
    echo "ATENCAO: modelo pesado ($model) — recomenda-se u2net ou birefnet-general-lite.\n";
    $lento = true;
}
if ($alpha) {
    echo "ATENCAO: alpha matting LIGADO — bem mais lento.\n";
    $lento = true;
}
if ($ppm) {
    echo "ATENCAO: pos-processar mascara LIGADO — adiciona custo.\n";
    $lento = true;
}
if (!$lento) {
    echo "OK: configuracao atual esta no perfil rapido (u2net + alpha/ppm off).\n";
}
