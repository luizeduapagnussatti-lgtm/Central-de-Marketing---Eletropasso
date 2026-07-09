<?php
declare(strict_types=1);

/**
 * Integracao com Hub de Precificacao (orcador_dev) via cURL.
 */
class HubPrecificacaoService
{
    private string $apiUrl;
    private string $token;
    private int $timeout = 5;

    public function __construct()
    {
        $this->apiUrl = marketing_config('hub_api_url', marketing_env('HUB_API_URL', '')) ?? '';
        $this->token = marketing_config('hub_api_token', marketing_env('HUB_API_TOKEN', '')) ?? '';
    }

    /**
     * @return array{success: bool, data?: array, error?: string}
     */
    public function buscarPorSku(string $sku): array
    {
        $sku = trim($sku);
        if ($sku === '') {
            return ['success' => false, 'error' => 'SKU vazio.'];
        }

        if ($this->apiUrl === '') {
            return ['success' => false, 'error' => 'URL do Hub nao configurada.'];
        }

        $url = $this->apiUrl . (str_contains($this->apiUrl, '?') ? '&' : '?')
            . 'sku=' . urlencode($sku) . '&token=' . urlencode($this->token);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            LoggerService::warning('Hub offline', ['sku' => $sku, 'erro' => $curlError]);
            return ['success' => false, 'error' => 'Hub indisponivel: ' . $curlError];
        }

        $json = json_decode($response, true);
        if (!is_array($json)) {
            return ['success' => false, 'error' => 'Resposta invalida do Hub.'];
        }

        if ($httpCode === 404 || ($json['status'] ?? '') === 'error') {
            return ['success' => false, 'error' => $json['error'] ?? 'SKU nao localizado.'];
        }

        if (($json['status'] ?? '') !== 'success' || !isset($json['data'])) {
            return ['success' => false, 'error' => 'Resposta inesperada do Hub.'];
        }

        return [
            'success' => true,
            'data'    => [
                'sku'               => (string) ($json['data']['sku'] ?? $sku),
                'nome_erp'          => (string) ($json['data']['nome_erp'] ?? ''),
                'preco_venda_atual' => (float) ($json['data']['preco_venda_atual'] ?? 0),
                'unidade'           => $this->normalizarUnidade(
                    (string) ($json['data']['unidade'] ?? $json['data']['unidade_venda'] ?? 'und')
                ),
            ],
        ];
    }

    private function normalizarUnidade(string $unidade): string
    {
        $u = strtoupper(trim($unidade));
        if ($u === '') {
            return 'und';
        }

        return match ($u) {
            'M', 'MT', 'MET', 'METRO', 'METROS', 'METR' => 'm',
            'RL', 'ROLO', 'ROLOS' => 'rl',
            'UN', 'UND', 'UNID', 'UNIDADE', 'PC', 'PÇ', 'PCA', 'PECA', 'PEÇA' => 'und',
            default => strtolower($u),
        };
    }
}
