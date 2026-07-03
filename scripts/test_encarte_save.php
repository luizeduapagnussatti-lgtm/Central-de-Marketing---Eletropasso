<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$service = new EncarteService();
$id = $service->salvar([
    'titulo_campanha' => 'Teste Integracao',
    'formato'         => '9x16',
    'max_itens'       => 4,
    'mes_vigencia'    => 7,
    'ano_vigencia'    => 2026,
    'itens'           => [[
        'nome_comercial'    => 'Lampada LED Teste',
        'preco_normal'      => 21.69,
        'preco_promocional' => 19.99,
        'unidade'           => 'und',
    ]],
]);

$encarte = $service->buscarPorId($id);
echo "Encarte ID: {$id}\n";
echo "Itens: " . count($encarte['itens']) . "\n";
echo "OK\n";
