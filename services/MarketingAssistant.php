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
    private string $rembgModel;
    private bool $rembgAlphaMatting;
    private bool $rembgPostProcessMask;
    private bool $rembgWhiteRefine;
    private int $rembgWhiteTolerance;
    private int $timeout = 25;

    public function __construct()
    {
        $this->apiKey = marketing_config('gemini_api_key', marketing_env('GEMINI_API_KEY', '')) ?? '';
        $this->model = marketing_config('gemini_model', marketing_env('GEMINI_MODEL', 'gemini-2.5-flash')) ?? 'gemini-2.5-flash';
        $this->rembgBin = marketing_config('rembg_bin', marketing_env('REMBG_BIN', 'rembg')) ?? 'rembg';
        $this->rembgModel = marketing_config('rembg_model', marketing_env('REMBG_MODEL', 'birefnet-general')) ?? 'birefnet-general';
        $this->rembgAlphaMatting = marketing_config_bool('rembg_alpha_matting', marketing_env('REMBG_ALPHA_MATTING', '1'));
        $this->rembgPostProcessMask = marketing_config_bool('rembg_post_process_mask', marketing_env('REMBG_POST_PROCESS_MASK', '1'));
        $this->rembgWhiteRefine = marketing_config_bool('rembg_white_refine', marketing_env('REMBG_WHITE_REFINE', '1'));
        $this->rembgWhiteTolerance = max(10, min(80, (int) (marketing_config('rembg_white_tolerance', marketing_env('REMBG_WHITE_TOLERANCE', '42')) ?? 42)));
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

        $modelos = $this->modelosRembgTentativa();
        $resultado = null;
        $modeloUsado = '';

        foreach ($modelos as $modelo) {
            $cmd = $this->montarComandoRembg($caminhoOriginal, $caminhoDestino, $modelo);
            $resultado = $this->executarComandoComTimeout($cmd, 120);
            $modeloUsado = $modelo;

            if ($resultado['ok'] && is_file($caminhoDestino)) {
                break;
            }

            if (is_file($caminhoDestino)) {
                @unlink($caminhoDestino);
            }
        }

        if ($resultado === null || !$resultado['ok'] || !is_file($caminhoDestino)) {
            $msgSaida = trim((string) ($resultado['output'] ?? ''));
            $erro = $msgSaida !== ''
                ? 'Rembg falhou: ' . $msgSaida
                : 'Rembg falhou (codigo ' . (int) ($resultado['exit_code'] ?? -1) . '). Verifique se o executavel esta instalado.';

            if (!empty($resultado['timed_out'])) {
                $erro = 'Rembg excedeu o tempo limite (120s). Use imagens menores ou verifique a instalacao do rembg.';
            }

            LoggerService::warning('Rembg falhou', [
                'exit_code' => $resultado['exit_code'] ?? -1,
                'timed_out' => $resultado['timed_out'] ?? false,
                'modelo'    => $modeloUsado,
                'cmd'       => $this->rembgBin . ' i -m [model] [input] [output]',
                'output'    => $msgSaida,
            ]);

            return ['ok' => false, 'erro' => $erro];
        }

        if ($this->rembgWhiteRefine) {
            $this->refinarPixelsFundoClaro($caminhoDestino, $caminhoOriginal);
        }

        LoggerService::info('Rembg OK', [
            'destino' => $caminhoDestino,
            'modelo'  => $modeloUsado,
            'refine'  => $this->rembgWhiteRefine,
        ]);

        return ['ok' => true, 'erro' => null];
    }

    /** @return list<string> */
    private function modelosRembgTentativa(): array
    {
        $principal = $this->normalizarModeloRembg($this->rembgModel);
        $fallback = 'u2net';

        if ($principal === $fallback) {
            return [$fallback];
        }

        return [$principal, $fallback];
    }

    private function normalizarModeloRembg(string $modelo): string
    {
        $modelo = strtolower(trim($modelo));
        $permitidos = [
            'birefnet-general',
            'birefnet-general-lite',
            'bria-rmbg',
            'isnet-general-use',
            'u2net',
            'u2netp',
            'silueta',
        ];

        return in_array($modelo, $permitidos, true) ? $modelo : 'birefnet-general';
    }

    private function montarComandoRembg(string $caminhoOriginal, string $caminhoDestino, string $modelo): string
    {
        $partes = [
            escapeshellarg($this->rembgBin),
            'i',
            '-m',
            escapeshellarg($modelo),
        ];

        if ($this->rembgPostProcessMask) {
            $partes[] = '-ppm';
        }

        if ($this->rembgAlphaMatting) {
            $partes[] = '-a';
            $partes[] = '-ab';
            $partes[] = '18';
            $partes[] = '-af';
            $partes[] = '235';
            $partes[] = '-ae';
            $partes[] = '12';
        }

        $partes[] = escapeshellarg($caminhoOriginal);
        $partes[] = escapeshellarg($caminhoDestino);

        return implode(' ', $partes) . ' 2>&1';
    }

    /**
     * Remove pixels claros residuais (ex.: fundo branco no miolo de rolos de cabo).
     */
    private function refinarPixelsFundoClaro(string $caminhoPng, string $caminhoOriginal): void
    {
        if (!function_exists('imagecreatefrompng') || !is_file($caminhoPng)) {
            return;
        }

        $img = @imagecreatefrompng($caminhoPng);
        if ($img === false) {
            return;
        }

        imagealphablending($img, false);
        imagesavealpha($img, true);

        $bg = $this->detectarCorFundoReferencia($caminhoOriginal);
        $tol = $this->rembgWhiteTolerance;
        $w = imagesx($img);
        $h = imagesy($img);
        $transparente = imagecolorallocatealpha($img, 0, 0, 0, 127);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha >= 127) {
                    continue;
                }

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($this->pixelEhFundoClaroResidual($r, $g, $b, $bg, $tol)) {
                    imagesetpixel($img, $x, $y, $transparente);
                }
            }
        }

        imagepng($img, $caminhoPng);
        imagedestroy($img);
    }

    /** @return array{r: int, g: int, b: int} */
    private function detectarCorFundoReferencia(string $caminhoOriginal): array
    {
        $src = $this->carregarImagemGd($caminhoOriginal);
        if ($src === false) {
            return ['r' => 255, 'g' => 255, 'b' => 255];
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if ($w < 2 || $h < 2) {
            imagedestroy($src);

            return ['r' => 255, 'g' => 255, 'b' => 255];
        }

        $pontos = [
            [0, 0],
            [$w - 1, 0],
            [0, $h - 1],
            [$w - 1, $h - 1],
            [(int) ($w / 2), 0],
            [(int) ($w / 2), $h - 1],
        ];

        $rs = 0;
        $gs = 0;
        $bs = 0;
        $n = 0;

        foreach ($pontos as [$x, $y]) {
            $c = imagecolorat($src, $x, $y);
            $rs += ($c >> 16) & 0xFF;
            $gs += ($c >> 8) & 0xFF;
            $bs += $c & 0xFF;
            $n++;
        }

        imagedestroy($src);

        return [
            'r' => (int) round($rs / $n),
            'g' => (int) round($gs / $n),
            'b' => (int) round($bs / $n),
        ];
    }

    /** @param array{r: int, g: int, b: int} $bg */
    private function pixelEhFundoClaroResidual(int $r, int $g, int $b, array $bg, int $tol): bool
    {
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        if ($max < 185) {
            return false;
        }

        if (($max - $min) > 28) {
            return false;
        }

        $dr = $r - $bg['r'];
        $dg = $g - $bg['g'];
        $db = $b - $bg['b'];

        return sqrt(($dr * $dr) + ($dg * $dg) + ($db * $db)) <= $tol;
    }

    /** @return \GdImage|false */
    private function carregarImagemGd(string $path)
    {
        $mime = mime_content_type($path);
        if ($mime === false) {
            $mime = '';
        }

        return match ($mime) {
            'image/png'             => @imagecreatefrompng($path),
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/webp'            => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default                 => @imagecreatefromjpeg($path) ?: @imagecreatefrompng($path),
        };
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

    /**
     * Executa comando externo com limite de tempo (evita travamento infinito no Rembg).
     *
     * @return array{ok: bool, exit_code: int, output: string, timed_out: bool}
     */
    private function executarComandoComTimeout(string $cmd, int $timeoutSegundos = 90): array
    {
        $timeoutSegundos = max(5, min(300, $timeoutSegundos));
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($proc)) {
            return ['ok' => false, 'exit_code' => -1, 'output' => 'Nao foi possivel iniciar o processo.', 'timed_out' => false];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $inicio = time();
        $timedOut = false;

        while (true) {
            $output .= stream_get_contents($pipes[1]) ?: '';
            $output .= stream_get_contents($pipes[2]) ?: '';

            $status = proc_get_status($proc);
            if (!$status['running']) {
                break;
            }

            if ((time() - $inicio) >= $timeoutSegundos) {
                $timedOut = true;
                proc_terminate($proc, 9);
                break;
            }

            usleep(100000);
        }

        $output .= stream_get_contents($pipes[1]) ?: '';
        $output .= stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($proc);
        if ($timedOut) {
            return ['ok' => false, 'exit_code' => $exitCode, 'output' => $output, 'timed_out' => true];
        }

        return [
            'ok'        => $exitCode === 0,
            'exit_code' => $exitCode,
            'output'    => $output,
            'timed_out' => false,
        ];
    }
}
