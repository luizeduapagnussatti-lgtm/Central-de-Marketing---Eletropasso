<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/app.php';

$encarte = [
    'titulo_campanha' => 'Super Ofertas',
    'validade_inicio' => '2026-07-01',
    'validade_fim' => '2026-07-31',
    'texto_legal_rodape' => 'Ofertas validas enquanto durarem os estoques.',
];
$opts = [
    'encarte_data' => $encarte,
    'modelo_config' => ['textos' => ['titulo_linha1' => 'Fallback Titulo']],
];

$checks = [];

$checks['titulo_campanha'] = ep_get_campanha_text_value('titulo_campanha', [], $opts) === 'Super Ofertas';
$checks['validade_periodo'] = str_contains(
    ep_get_campanha_text_value('validade_periodo', [], $opts),
    'Valido de'
);
$checks['texto_legal_rodape'] = str_contains(
    ep_get_campanha_text_value('texto_legal_rodape', [], $opts),
    'estoques'
);

$objCampanha = [
    'name' => 'titulo_linha1',
    'text' => 'Static',
    'isCampanhaText' => true,
    'textType' => 'titulo_campanha',
];
$checks['resolve_campanha_flag'] = ep_resolve_fabric_text_content($objCampanha, [], $opts) === 'Super Ofertas';

$objLegacy = ['name' => 'titulo_linha1', 'text' => 'Static Only'];
$checks['resolve_legacy_name'] = ep_resolve_fabric_text_content($objLegacy, [], $opts) === 'Super Ofertas';

$objProduct = [
    'name' => 'nome_produto',
    'isDynamicText' => true,
    'textType' => 'nome_produto',
    'linkedZone' => 1,
];
$checks['product_untouched'] = ep_resolve_fabric_text_content(
    $objProduct,
    [['nome_comercial' => 'Prod A', 'preco_normal' => 10, 'preco_promocional' => 8, 'unidade' => 'und']],
    $opts
) === 'Prod A';

$checks['format_empty'] = ep_format_validade_periodo(null, null) === '';
$checks['format_fim_only'] = str_contains(ep_format_validade_periodo(null, '2026-07-31'), 'Valido ate');

$failed = array_keys(array_filter($checks, static fn ($ok) => !$ok));

if ($failed !== []) {
    fwrite(STDERR, 'FAILED: ' . implode(', ', $failed) . PHP_EOL);
    exit(1);
}

echo 'All campanha zone checks passed (' . count($checks) . ').' . PHP_EOL;
