<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$assistant = new MarketingAssistant();
$entrada = 'LUMANTI LAMPADA GELADEIRA 1.5W E14 REF.LLFT7015W';
$saida = $assistant->normalizarNome($entrada);

echo "Entrada: {$entrada}\n";
echo "Saida:   {$saida}\n";

$titulos = $assistant->gerarTitulosCampanha(['Lampada LED', 'Tomada 10A']);
echo "Titulos: " . implode(' | ', $titulos) . "\n";
