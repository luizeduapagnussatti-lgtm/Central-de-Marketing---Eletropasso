-- sqlfluff:dialect:mysql
-- Migration 004 — descricao, config_visual e updated_at em modelos_layout
-- Reexecutavel: adiciona colunas apenas se ausentes; preenche config padrao vazia.

USE `central_marketing_eletropasso`;

SET @ep_col_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'modelos_layout'
    AND COLUMN_NAME = 'descricao'
);
SET @ep_sql = IF(
  @ep_col_exists = 0,
  'ALTER TABLE `modelos_layout` ADD COLUMN `descricao` VARCHAR(500) NULL AFTER `nome_exibicao`',
  'SELECT 1'
);
PREPARE ep_stmt FROM @ep_sql;
EXECUTE ep_stmt;
DEALLOCATE PREPARE ep_stmt;

SET @ep_col_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'modelos_layout'
    AND COLUMN_NAME = 'config_visual'
);
SET @ep_sql = IF(
  @ep_col_exists = 0,
  'ALTER TABLE `modelos_layout` ADD COLUMN `config_visual` JSON NULL AFTER `formatos_suportados`',
  'SELECT 1'
);
PREPARE ep_stmt FROM @ep_sql;
EXECUTE ep_stmt;
DEALLOCATE PREPARE ep_stmt;

SET @ep_col_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'modelos_layout'
    AND COLUMN_NAME = 'updated_at'
);
SET @ep_sql = IF(
  @ep_col_exists = 0,
  'ALTER TABLE `modelos_layout` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`',
  'SELECT 1'
);
PREPARE ep_stmt FROM @ep_sql;
EXECUTE ep_stmt;
DEALLOCATE PREPARE ep_stmt;

UPDATE `modelos_layout`
SET
  `descricao` = 'Layout com produtos flutuantes, precos em destaque e identidade Eletropasso.',
  `config_visual` = JSON_OBJECT(
    'cores', JSON_OBJECT(
      'primary', '#b91c1c',
      'dark', '#7f1d1d',
      'fundo', '#ffffff',
      'fundo_escuro', '#1f2937',
      'preco_bg', '#16a34a',
      'preco_texto', '#ffffff',
      'badge_bg', '#059669',
      'badge_texto', '#ffffff'
    ),
    'textos', JSON_OBJECT(
      'badge_oferta', 'Oferta Especial!',
      'clube_badge', 'Clube Eletropasso',
      'mostrar_clube', false
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
WHERE `codigo` = 'modelo_01'
  AND (`config_visual` IS NULL OR JSON_LENGTH(`config_visual`) = 0);

UPDATE `modelos_layout`
SET
  `descricao` = 'Feed quadrado 1:1 com 3 produtos no topo e 2 destaques grandes na base escura, sem bordas.',
  `config_visual` = JSON_OBJECT(
    'cores', JSON_OBJECT(
      'primary', '#b91c1c',
      'dark', '#7f1d1d',
      'fundo', '#ffffff',
      'fundo_escuro', '#1f2937',
      'preco_bg', '#b91c1c',
      'preco_texto', '#ffffff',
      'badge_bg', '#ffffff',
      'badge_texto', '#b91c1c'
    ),
    'textos', JSON_OBJECT(
      'badge_oferta', 'Oferta',
      'clube_badge', 'Clube Eletropasso',
      'mostrar_clube', true
    ),
    'icones', JSON_OBJECT(
      'auto', false,
      'mapa', JSON_OBJECT()
    )
  )
WHERE `codigo` = 'modelo_02'
  AND (`config_visual` IS NULL OR JSON_LENGTH(`config_visual`) = 0);
