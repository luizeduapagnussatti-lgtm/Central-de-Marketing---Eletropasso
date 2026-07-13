<?php
declare(strict_types=1);

/**
 * Gerencia configuracoes do sistema (config_sistema + .env).
 */
class ConfigService
{
    /** @return array<string, string> */
    public function listarTodas(): array
    {
        $st = marketing_pdo()->query('SELECT chave, valor, descricao FROM config_sistema ORDER BY chave ASC');
        $rows = $st->fetchAll();
        $config = [];

        foreach ($rows as $row) {
            $config[$row['chave']] = [
                'valor'     => $row['valor'],
                'descricao' => $row['descricao'],
            ];
        }

        return $config;
    }

    /** @param array<string, string> $dados */
    public function salvar(array $dados): void
    {
        $permitidas = [
            'gemini_api_key', 'gemini_model', 'hub_api_url', 'hub_api_token',
            'max_upload_size_mb', 'watermark_rodape', 'rembg_bin', 'rembg_model',
            'rembg_alpha_matting', 'rembg_post_process_mask', 'rembg_white_refine',
            'rembg_max_edge',
        ];

        foreach ($dados as $chave => $valor) {
            if (!in_array($chave, $permitidas, true)) {
                continue;
            }
            marketing_config_set($chave, trim((string) $valor));
        }

        LoggerService::info('Configuracoes atualizadas');
    }
}
