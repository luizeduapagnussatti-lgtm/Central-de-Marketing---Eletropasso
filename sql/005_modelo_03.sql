-- sqlfluff:dialect:mysql
-- Modelo 03 — Premium Escuro (grid 3x2, formato vertical 9:16)
-- Requer colunas descricao/config_visual (001 atualizado ou 004 aplicado).

USE `central_marketing_eletropasso`;

INSERT INTO `modelos_layout`
  (
    `codigo`,
    `versao`,
    `nome_exibicao`,
    `descricao`,
    `arquivo_template`,
    `formatos_suportados`,
    `max_itens_default`,
    `config_visual`
  )
VALUES
  (
    'modelo_03',
    '1.0',
    'Premium Escuro — Grid 3x2',
    'Encarte vertical premium com fundo escuro, titulo 3D, grid de 6 produtos com features e rodape institucional.',
    'modelo_03.php',
    '["9x16","status"]',
    6,
    JSON_OBJECT(
      'cores', JSON_OBJECT(
        'primary', '#dc2626',
        'dark', '#991b1b',
        'fundo', '#030b17',
        'fundo_escuro', '#071326',
        'preco_bg', '#dc2626',
        'preco_texto', '#ffffff',
        'badge_bg', '#1e3a8a',
        'badge_texto', '#ffffff'
      ),
      'textos', JSON_OBJECT(
        'badge_oferta', 'OFERTAS IMPERDIVEIS!',
        'clube_badge', 'Clube Eletropasso',
        'mostrar_clube', false,
        'titulo_linha1', 'PROMOCAO',
        'titulo_linha2', 'FECHA MES',
        'faixa_oferta', 'OFERTAS IMPERDIVEIS!',
        'subtitulo', 'QUALIDADE, SEGURANCA E OS MELHORES PRECOS VOCE ENCONTRA AQUI!',
        'footer_endereco', 'Av. Brasil, Centro',
        'footer_cidade', 'Passo Fundo - RS',
        'footer_whatsapp', '(54) 9 9999-9999'
      ),
      'icones', JSON_OBJECT(
        'auto', true,
        'mapa', JSON_OBJECT(
          'led', '💡',
          'wifi', '📶',
          'tomada', '🔌',
          'cabo', '⚡'
        )
      )
    )
  )
ON DUPLICATE KEY UPDATE
  `nome_exibicao` = VALUES(`nome_exibicao`),
  `descricao` = VALUES(`descricao`),
  `arquivo_template` = VALUES(`arquivo_template`),
  `formatos_suportados` = VALUES(`formatos_suportados`),
  `max_itens_default` = VALUES(`max_itens_default`),
  `config_visual` = VALUES(`config_visual`);
