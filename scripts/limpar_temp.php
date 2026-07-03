<?php
declare(strict_types=1);

/**
 * Script CLI para limpar arquivos temporarios antigos.
 * Uso: php scripts/limpar_temp.php
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

$removidos = EncarteRenderService::limparTemp();
echo "Arquivos temp removidos: {$removidos}\n";
