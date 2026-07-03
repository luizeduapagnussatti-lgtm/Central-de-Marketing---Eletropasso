-- sqlfluff:dialect:mysql
-- Modelo 02 — Estilo Livre (3 topo + 2 destaque, formato 1:1)
-- Reexecutavel: atualiza metadados sem sobrescrever config_visual customizado.

USE `central_marketing_eletropasso`;

INSERT INTO `modelos_layout`
  (`codigo`, `versao`, `nome_exibicao`, `arquivo_template`, `formatos_suportados`, `max_itens_default`)
VALUES
  ('modelo_02', '1.0', 'Estilo Livre — Feed Quadrado', 'modelo_02.php',
   '["1x1"]', 5)
ON DUPLICATE KEY UPDATE
  `nome_exibicao` = VALUES(`nome_exibicao`),
  `arquivo_template` = VALUES(`arquivo_template`),
  `formatos_suportados` = VALUES(`formatos_suportados`),
  `max_itens_default` = VALUES(`max_itens_default`);
