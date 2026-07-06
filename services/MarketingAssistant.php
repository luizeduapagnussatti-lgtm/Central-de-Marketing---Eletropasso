<?php
declare(strict_types=1);

/**
 * Integracao com Google Gemini (texto) e Rembg (remocao de fundo offline).
 */
class MarketingAssistant
{
    private const SYSTEM_NOME = <<<'TXT'
Voce e um assistente de marketing da Eletropasso (loja de materiais eletricos e iluminacao).
Sua tarefa e transformar nomes tecnicos de ERP em nomes comerciais curtos (maximo 60 caracteres), claros e atraentes para encartes.
Mantenha a marca, potencia (W) e tensao (V) quando relevantes.
Retorne EXATAMENTE APENAS o nome comercial final.
Nao use aspas, nao use formatacao markdown, nao adicione explicacoes.
TXT;

    private const SYSTEM_TITULOS = <<<'TXT'
Voce e o copywriter principal da Eletropasso.
Crie 3 opcoes de titulos curtos e de alto impacto para uma campanha promocional, baseando-se na lista de produtos fornecida.
O foco e material eletrico e construcao.
Cada titulo deve ser direto, comercial e adequado para encarte promocional.
TXT;

    private string $apiKey;
    private string $model;
    private string $rembgBin;
    private int $timeout = 25;

    public function __construct()
    {
        $this->apiKey = marketing_config('gemini_api_key', marketing_env('GEMINI_API_KEY', '')) ?? '';
        $this->model = marketing_config('gemini_model', marketing_env('GEMINI_MODEL', 'gemini-2.5-flash')) ?? 'gemini-2.5-flash';
        $this->rembgBin = marketing_config('rembg_bin', marketing_env('REMBG_BIN', 'rembg')) ?? 'rembg';
    }

    public function normalizarNome(string $nomeErp): string
    {
        if ($this->apiKey === '') {
            return $this->fallbackNormalizarNome($nomeErp);
        }

        try {
            $resultado = $this->chamarGemini(
                self::SYSTEM_NOME,
                trim($nomeErp),
                [
                    'temperature'     => 0.1,
                    'maxOutputTokens' => 128,
                    'thinkingConfig'  => ['thinkingBudget' => 0],
                ]
            );

            if ($resultado !== '') {
                $nome = $this->sanitizarNomeComercial($resultado);
                if (mb_strlen($nome) >= 3) {
                    return $nome;
                }
            }
        } catch (Throwable $e) {
            LoggerService::warning('Gemini normalizarNome falhou', ['erro' => $e->getMessage()]);
        }

        return $this->fallbackNormalizarNome($nomeErp);
    }

    /** @return list<string> */
    public function gerarTitulosCampanha(array $nomes): array
    {
        if ($this->apiKey === '') {
            return $this->fallbackTitulos($nomes);
        }

        $lista = implode(', ', array_slice(array_map('strval', $nomes), 0, 20));
        $userPrompt = 'Produtos do encarte: ' . $lista . '.';

        try {
            $json = $this->chamarGeminiJson(
                self::SYSTEM_TITULOS,
                $userPrompt,
                [
                    'temperature'      => 0.7,
                    'maxOutputTokens'  => 512,
                    'thinkingConfig'   => ['thinkingBudget' => 0],
                    'responseMimeType' => 'application/json',
                    'responseSchema'   => [
                        'type'       => 'OBJECT',
                        'properties' => [
                            'opcoes' => [
                                'type'  => 'ARRAY',
                                'items' => ['type' => 'STRING'],
                            ],
                        ],
                        'required' => ['opcoes'],
                    ],
                ]
            );

            if (isset($json['opcoes']) && is_array($json['opcoes']) && count($json['opcoes']) >= 1) {
                return array_slice(array_map('strval', $json['opcoes']), 0, 3);
            }
        } catch (Throwable $e) {
            LoggerService::warning('Gemini gerarTitulos falhou', ['erro' => $e->getMessage()]);
        }

        return $this->fallbackTitulos($nomes);
    }

    /**
     * Remove fundo via Rembg CLI (offline, sem API Gemini).
     *
     * @return array{ok: bool, erro: string|null}
     */
    public function removerFundoImagem(string $caminhoOriginal, string $caminhoDestino): array
    {
        if (!is_file($caminhoOriginal)) {
            return ['ok' => false, 'erro' => 'Arquivo original nao encontrado.'];
        }

        if ($this->rembgBin === '') {
            LoggerService::warning('Rembg nao configurado (REMBG_BIN vazio)');
            return ['ok' => false, 'erro' => 'Rembg nao configurado. Defina REMBG_BIN em Configuracoes.'];
        }

        $dir = dirname($caminhoDestino);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Saida sempre PNG com transparencia
        if (!str_ends_with(strtolower($caminhoDestino), '.png')) {
            $caminhoDestino = preg_replace('/\.[^.]+$/', '.png', $caminhoDestino) ?? $caminhoDestino . '.png';
        }

        $rembgArg = escapeshellarg($this->rembgBin);
        $inputArg = escapeshellarg($caminhoOriginal);
        $outputArg = escapeshellarg($caminhoDestino);

        $cmd = "{$rembgArg} i {$inputArg} {$outputArg} 2>&1";
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($caminhoDestino)) {
            $msgSaida = trim(implode("\n", $output));
            $erro = $msgSaida !== ''
                ? 'Rembg falhou: ' . $msgSaida
                : 'Rembg falhou (codigo ' . $exitCode . '). Verifique se o executavel esta instalado.';

            LoggerService::warning('Rembg falhou', [
                'exit_code' => $exitCode,
                'cmd'       => $this->rembgBin . ' i [input] [output]',
                'output'    => $msgSaida,
            ]);

            return ['ok' => false, 'erro' => $erro];
        }

        LoggerService::info('Rembg OK', ['destino' => $caminhoDestino]);

        return ['ok' => true, 'erro' => null];
    }

    /**
     * @param array<string, mixed> $generationConfig
     */
    private function chamarGemini(string $systemInstruction, string $userPrompt, array $generationConfig): string
    {
        $body = [
            'system_instruction' => [
                'parts' => [['text' => trim($systemInstruction)]],
            ],
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $userPrompt]],
            ]],
            'generationConfig' => $generationConfig,
        ];

        $response = $this->requestGemini($body);
        $parts = $response['candidates'][0]['content']['parts'] ?? [];
        $texto = '';

        foreach ($parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                $texto .= $part['text'];
            }
        }

        return trim($texto);
    }

    /**
     * @param array<string, mixed> $generationConfig
     * @return array<string, mixed>
     */
    private function chamarGeminiJson(string $systemInstruction, string $userPrompt, array $generationConfig): array
    {
        $texto = $this->chamarGemini($systemInstruction, $userPrompt, $generationConfig);

        if ($texto === '') {
            return [];
        }

        $json = json_decode($texto, true);
        if (is_array($json)) {
            return $json;
        }

        throw new RuntimeException('Gemini retornou JSON invalido para titulos.');
    }

    private function requestGemini(array $body): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . urlencode($this->model) . ':generateContent?key=' . urlencode($this->apiKey);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => $this->timeout,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            $detalhe = is_string($response) ? mb_substr($response, 0, 500) : '';
            throw new RuntimeException('Gemini HTTP ' . $httpCode . ($detalhe !== '' ? ': ' . $detalhe : ''));
        }

        $json = json_decode($response, true);
        if (!is_array($json)) {
            throw new RuntimeException('Resposta Gemini invalida.');
        }

        return $json;
    }

    private function sanitizarNomeComercial(string $nome): string
    {
        $nome = trim($nome);
        $nome = trim($nome, "\"'`");
        $nome = preg_replace('/^#+\s*/', '', $nome) ?? $nome;
        $nome = preg_replace('/[*_`]+/', '', $nome) ?? $nome;
        $nome = preg_replace('/\s+/', ' ', $nome) ?? $nome;

        return mb_substr(trim($nome), 0, 60);
    }

    private function fallbackNormalizarNome(string $nomeErp): string
    {
        $nome = mb_convert_case(mb_strtolower(trim($nomeErp)), MB_CASE_TITLE, 'UTF-8');

        return mb_substr($nome, 0, 60);
    }

    /** @return list<string> */
    private function fallbackTitulos(array $nomes): array
    {
        $count = count($nomes);

        return [
            'Ofertas Eletropasso — ' . $count . ' produtos em promocao',
            'Promocao Imperdivel — Materiais Eletricos',
            'Super Ofertas — Confira os precos especiais',
        ];
    }
}
