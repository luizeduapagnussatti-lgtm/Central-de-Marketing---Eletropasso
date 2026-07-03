<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$recurso = $_GET['recurso'] ?? '';
$acao = $_GET['acao'] ?? '';

try {
    match ($recurso) {
        'encarte' => (function () use ($acao) {
            require_once marketing_path('controllers/encarte_controller.php');
            (new EncarteController())->handle($acao);
        })(),
        'config' => (function () use ($acao) {
            require_once marketing_path('controllers/config_controller.php');
            (new ConfigController())->handle($acao);
        })(),
        'modelo' => (function () use ($acao) {
            require_once marketing_path('controllers/modelo_controller.php');
            (new ModeloController())->handle($acao);
        })(),
        default => marketing_json_response(false, null, 'Recurso nao encontrado.', 404),
    };
} catch (Throwable $e) {
    LoggerService::error('API error', ['erro' => $e->getMessage()]);
    marketing_json_response(false, null, 'Erro interno.', 500);
}
