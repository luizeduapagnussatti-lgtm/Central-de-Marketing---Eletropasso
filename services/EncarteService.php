<?php
declare(strict_types=1);

/**
 * CRUD e operacoes de negocio para encartes e itens.
 */
class EncarteService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? marketing_pdo();
    }

    public function listarAgrupadosPorMes(): array
    {
        $st = $this->pdo->query(
            'SELECT * FROM encartes ORDER BY ano_vigencia DESC, mes_vigencia DESC, created_at DESC'
        );
        $rows = $st->fetchAll();
        $grupos = [];

        foreach ($rows as $row) {
            $chave = sprintf('%04d-%02d', (int) $row['ano_vigencia'], (int) $row['mes_vigencia']);
            $grupos[$chave]['label'] = $this->mesLabel((int) $row['mes_vigencia'], (int) $row['ano_vigencia']);
            $grupos[$chave]['encartes'][] = $row;
        }

        return $grupos;
    }

    public function buscarPorId(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM encartes WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        $encarte = $st->fetch();

        if ($encarte === false) {
            return null;
        }

        $encarte['itens'] = $this->listarItens($id);

        return $encarte;
    }

    public function listarItens(int $encarteId): array
    {
        $st = $this->pdo->prepare(
            'SELECT * FROM encarte_itens WHERE encarte_id = ? ORDER BY ordem_exibicao ASC, id ASC'
        );
        $st->execute([$encarteId]);

        return $st->fetchAll();
    }

    public function salvar(array $payload): int
    {
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        $mes = (int) ($payload['mes_vigencia'] ?? (int) date('n'));
        $ano = (int) ($payload['ano_vigencia'] ?? (int) date('Y'));

        if ($id > 0) {
            $st = $this->pdo->prepare(
                'UPDATE encartes SET
                    titulo_campanha = ?, modelo_layout = ?, formato = ?, max_itens = ?,
                    validade_inicio = ?, validade_fim = ?, texto_legal_rodape = ?,
                    mes_vigencia = ?, ano_vigencia = ?, status = ?
                 WHERE id = ?'
            );
            $st->execute([
                $payload['titulo_campanha'],
                $payload['modelo_layout'] ?? 'modelo_01',
                $payload['formato'],
                (int) ($payload['max_itens'] ?? 12),
                $payload['validade_inicio'] ?? null,
                $payload['validade_fim'] ?? null,
                $payload['texto_legal_rodape'] ?? marketing_config('watermark_rodape'),
                $mes,
                $ano,
                $payload['status'] ?? 'rascunho',
                $id,
            ]);
        } else {
            $st = $this->pdo->prepare(
                'INSERT INTO encartes
                    (titulo_campanha, modelo_layout, formato, max_itens, validade_inicio, validade_fim,
                     texto_legal_rodape, mes_vigencia, ano_vigencia, status, versao, encarte_pai_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                $payload['titulo_campanha'],
                $payload['modelo_layout'] ?? 'modelo_01',
                $payload['formato'],
                (int) ($payload['max_itens'] ?? 12),
                $payload['validade_inicio'] ?? null,
                $payload['validade_fim'] ?? null,
                $payload['texto_legal_rodape'] ?? marketing_config('watermark_rodape'),
                $mes,
                $ano,
                $payload['status'] ?? 'rascunho',
                (int) ($payload['versao'] ?? 1),
                $payload['encarte_pai_id'] ?? null,
            ]);
            $id = (int) $this->pdo->lastInsertId();
        }

        if (isset($payload['itens']) && is_array($payload['itens'])) {
            $this->salvarItens($id, $payload['itens']);
        }

        return $id;
    }

    public function salvarItens(int $encarteId, array $itens): void
    {
        $this->pdo->prepare('DELETE FROM encarte_itens WHERE encarte_id = ?')->execute([$encarteId]);

        $st = $this->pdo->prepare(
            'INSERT INTO encarte_itens
                (encarte_id, ordem_exibicao, sku, nome_erp, nome_comercial, descricao_complementar,
                 preco_normal, preco_promocional, unidade, caminho_foto_original, caminho_foto_limpa,
                 processamento_imagem_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $ordem = 0;
        foreach ($itens as $item) {
            if (empty($item['nome_comercial'])) {
                continue;
            }
            $st->execute([
                $encarteId,
                $ordem++,
                $item['sku'] ?? null,
                $item['nome_erp'] ?? null,
                $item['nome_comercial'],
                $item['descricao_complementar'] ?? null,
                (float) ($item['preco_normal'] ?? 0),
                (float) ($item['preco_promocional'] ?? 0),
                $item['unidade'] ?? 'und',
                $item['caminho_foto_original'] ?? null,
                $item['caminho_foto_limpa'] ?? '',
                $item['processamento_imagem_status'] ?? 'pendente',
            ]);
        }

        $this->pdo->prepare('UPDATE encartes SET quantidade_itens = ? WHERE id = ?')
            ->execute([$ordem, $encarteId]);
    }

    public function criarNovaVersao(int $encartePaiId): int
    {
        $original = $this->buscarPorId($encartePaiId);
        if ($original === null) {
            throw new RuntimeException('Encarte original nao encontrado.');
        }

        $novaVersao = (int) $original['versao'] + 1;
        $payload = [
            'titulo_campanha'     => $original['titulo_campanha'],
            'modelo_layout'       => $original['modelo_layout'],
            'formato'             => $original['formato'],
            'max_itens'           => $original['max_itens'],
            'validade_inicio'     => $original['validade_inicio'],
            'validade_fim'        => $original['validade_fim'],
            'texto_legal_rodape'  => $original['texto_legal_rodape'],
            'mes_vigencia'        => $original['mes_vigencia'],
            'ano_vigencia'        => $original['ano_vigencia'],
            'status'              => 'rascunho',
            'versao'              => $novaVersao,
            'encarte_pai_id'      => $encartePaiId,
            'itens'               => $original['itens'],
        ];

        return $this->salvar($payload);
    }

    public function atualizarStatus(int $id, string $status, ?string $erro = null, ?int $tempoMs = null, ?string $caminhoImagem = null): void
    {
        $st = $this->pdo->prepare(
            'UPDATE encartes SET status = ?, erro_geracao = ?, tempo_geracao_ms = ?,
             caminho_imagem_final = COALESCE(?, caminho_imagem_final) WHERE id = ?'
        );
        $st->execute([$status, $erro, $tempoMs, $caminhoImagem, $id]);
    }

    public function listarModelos(): array
    {
        return (new ModeloLayoutService($this->pdo))->listarAtivos();
    }

    public function buscarModeloPorCodigo(string $codigo): ?array
    {
        return (new ModeloLayoutService($this->pdo))->buscarPorCodigoAtivo($codigo);
    }

    /** Busca modelo mesmo inativo (edicao/render de encartes existentes). */
    public function buscarModeloPorCodigoQualquer(string $codigo): ?array
    {
        return (new ModeloLayoutService($this->pdo))->buscarPorCodigo($codigo);
    }

    public function resolverArquivoTemplate(string $codigoModelo): string
    {
        return (new ModeloLayoutService($this->pdo))->resolverArquivoTemplate($codigoModelo);
    }

    public function excluir(int $id): bool
    {
        $encarte = $this->buscarPorId($id);
        if ($encarte === null) {
            return false;
        }

        if (!empty($encarte['caminho_imagem_final'])) {
            $caminho = marketing_path($encarte['caminho_imagem_final']);
            if (is_file($caminho)) {
                @unlink($caminho);
            }
        }

        $st = $this->pdo->prepare('DELETE FROM encartes WHERE id = ?');
        $st->execute([$id]);

        return $st->rowCount() > 0;
    }

    private function mesLabel(int $mes, int $ano): string
    {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Marco', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return ($meses[$mes] ?? (string) $mes) . ' / ' . $ano;
    }
}
