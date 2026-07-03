<?php
declare(strict_types=1);

$base = getenv('USERPROFILE') . '/.cursor/projects/c-xampp-htdocs-Central-de-marketing-dev/assets';
$brandDir = __DIR__ . '/../assets/brand';
$rootDir = dirname(__DIR__);

$imports = [
    'logo_eletropasso_branca_source.png' => ['*Logo_eletropasso_branca*.png', '*logo*branca*.png'],
    'logo_eletropasso_preta_source.png'  => ['*logo*preto*.png', '*logo*preta*.png', '*eletropasso*preto*.png'],
];

foreach ($imports as $destName => $patterns) {
    $copied = false;
    foreach ($patterns as $pattern) {
        $matches = glob($base . '/' . $pattern);
        if ($matches === [] || !is_file($matches[0])) {
            continue;
        }
        copy($matches[0], $brandDir . '/' . $destName);
        echo "OK: {$destName} <- " . basename($matches[0]) . "\n";
        $copied = true;
        break;
    }
    if (!$copied && $destName === 'logo_eletropasso_preta_source.png') {
        echo "ERRO: logo preta nao encontrada. Copie logo-eletropasso-preto.png para assets/brand/logo_eletropasso_preta_source.png\n";
        exit(1);
    }
}

passthru('node ' . escapeshellarg($rootDir . '/bin/process_logos.js'), $exitCode);
if ($exitCode !== 0) {
    exit(1);
}

echo "Concluido\n";
