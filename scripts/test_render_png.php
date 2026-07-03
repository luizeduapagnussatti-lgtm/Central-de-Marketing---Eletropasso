<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$service = new EncarteService();
$render = new EncarteRenderService($service);

$encarte = $service->buscarPorId(2);
if ($encarte === null) {
    $id = $service->salvar([
        'titulo_campanha' => 'Teste Render',
        'formato'         => '1x1',
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
}

echo "Gerando encarte ID {$encarte['id']}...\n";

try {
    $result = $render->gerar((int) $encarte['id']);
    echo "PNG: {$result['caminho']}\n";
    echo "Tempo: {$result['tempo_ms']}ms\n";
    echo "Arquivo existe: " . (is_file(marketing_path($result['caminho'])) ? 'SIM' : 'NAO') . "\n";
    echo "OK\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
