<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';
require_once dirname(__DIR__) . '/controllers/encarte_controller.php';

class ConfigController
{
    private ConfigService $configService;

    public function __construct()
    {
        $this->configService = new ConfigService();
    }

    public function handle(string $action): void
    {
        match ($action) {
            'listar'       => $this->listar(),
            'salvar'       => $this->salvar(),
            'diagnosticos' => $this->diagnosticos(),
            default        => marketing_json_response(false, null, 'Acao invalida.', 404),
        };
    }

    private function listar(): void
    {
        marketing_json_response(true, ['config' => $this->configService->listarTodas()]);
    }

    private function salvar(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            marketing_json_response(false, null, 'Payload invalido.', 422);
        }

        try {
            $this->configService->salvar($payload);
            marketing_json_response(true, ['salvo' => true]);
        } catch (Throwable $e) {
            marketing_json_response(false, null, $e->getMessage(), 500);
        }
    }

    private function diagnosticos(): void
    {
        $cfg = $this->configService->listarTodas();
        $geminiKey = trim((string) ($cfg['gemini_api_key']['valor'] ?? ''));
        $hubUrl = trim((string) ($cfg['hub_api_url']['valor'] ?? marketing_env('HUB_API_URL', '') ?? ''));
        $hubToken = trim((string) ($cfg['hub_api_token']['valor'] ?? marketing_env('HUB_API_TOKEN', '') ?? ''));
        $rembgBin = trim((string) ($cfg['rembg_bin']['valor'] ?? marketing_env('REMBG_BIN', 'rembg') ?? 'rembg'));

        marketing_json_response(true, [
            'integracoes' => [
                'gemini' => [
                    'ok'    => $geminiKey !== '',
                    'label' => 'Gemini (nomes e titulos)',
                ],
                'hub' => [
                    'ok'    => $hubUrl !== '' && $hubToken !== '',
                    'label' => 'Hub de Precificacao',
                ],
            ],
            'servicos' => [
                'rembg' => $this->testarExecutavel($rembgBin, ['--help', '-h']),
                'node'  => $this->testarExecutavel(
                    marketing_env('NODE_BIN', 'node') ?? 'node',
                    ['--version']
                ),
            ],
        ]);
    }

    /** @param list<string> $argsTeste */
    private function testarExecutavel(string $bin, array $argsTeste): array
    {
        $bin = trim($bin);
        if ($bin === '') {
            return ['ok' => false, 'label' => $bin, 'detalhe' => 'Nao configurado'];
        }

        $arg = escapeshellarg($argsTeste[0] ?? '--version');
        $cmd = escapeshellarg($bin) . ' ' . $arg . ' 2>&1';
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $ok = $exitCode === 0;
        $detalhe = $ok
            ? trim(explode("\n", implode("\n", $output))[0] ?? '')
            : 'Comando nao encontrado ou falhou';

        return [
            'ok'      => $ok,
            'label'   => basename(str_replace('\\', '/', $bin)),
            'detalhe' => $detalhe !== '' ? $detalhe : ($ok ? 'Disponivel' : 'Indisponivel'),
        ];
    }
}
