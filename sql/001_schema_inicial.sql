-- sqlfluff:dialect:mysql
-- ================================================================
-- Central de Marketing Eletropasso — Schema v1.0
-- Database: central_marketing_eletropasso
-- ================================================================

CREATE DATABASE IF NOT EXISTS `central_marketing_eletropasso`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `central_marketing_eletropasso`;

CREATE TABLE IF NOT EXISTS `modelos_layout` (
  `id`                  INT            NOT NULL AUTO_INCREMENT,
  `codigo`              VARCHAR(50)    NOT NULL UNIQUE COMMENT 'Ex: modelo_01',
  `versao`              VARCHAR(10)    NOT NULL DEFAULT '1.0',
  `nome_exibicao`       VARCHAR(100)   NOT NULL,
  `descricao`           VARCHAR(500)   NULL,
  `arquivo_template`    VARCHAR(255)   NOT NULL COMMENT 'Relativo a views/templates/',
  `formatos_suportados` JSON           NOT NULL COMMENT '["9x16","1x1","a4","16x9","status"]',
  `config_visual`       JSON           NULL,
  `max_itens_default`   TINYINT        NOT NULL DEFAULT 12,
  `ativo`               TINYINT(1)     NOT NULL DEFAULT 1,
  `created_at`          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP      NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `modelos_layout`
  (`codigo`, `versao`, `nome_exibicao`, `arquivo_template`, `formatos_suportados`, `max_itens_default`)
VALUES
  ('modelo_01', '1.0', 'Grade Flutuante Eletropasso', 'modelo_01.php',
   '["9x16","1x1","a4","16x9","status"]', 12)
ON DUPLICATE KEY UPDATE
  `nome_exibicao` = VALUES(`nome_exibicao`),
  `arquivo_template` = VALUES(`arquivo_template`),
  `formatos_suportados` = VALUES(`formatos_suportados`),
  `max_itens_default` = VALUES(`max_itens_default`);

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

CREATE TABLE IF NOT EXISTS `encartes` (
  `id`                   INT            NOT NULL AUTO_INCREMENT,
  `titulo_campanha`      VARCHAR(255)   NOT NULL,
  `modelo_layout`        VARCHAR(50)    NOT NULL COMMENT 'FK logica para modelos_layout.codigo',
  `formato`              VARCHAR(10)    NOT NULL COMMENT '9x16 | 1x1 | a4 | 16x9 | status',
  `max_itens`            TINYINT        NOT NULL DEFAULT 12,
  `quantidade_itens`     TINYINT        NOT NULL DEFAULT 0,
  `validade_inicio`      DATE           NULL,
  `validade_fim`         DATE           NULL,
  `texto_legal_rodape`   VARCHAR(500)   NULL,
  `caminho_imagem_final` VARCHAR(255)   NULL,
  `mes_vigencia`         TINYINT        NOT NULL COMMENT '1-12',
  `ano_vigencia`         YEAR           NOT NULL,
  `status`               ENUM('rascunho','gerando','concluido','erro') NOT NULL DEFAULT 'rascunho',
  `versao`               SMALLINT       NOT NULL DEFAULT 1,
  `encarte_pai_id`       INT            NULL COMMENT 'Preenchido ao gerar V2+ de um encarte existente',
  `tempo_geracao_ms`     INT            NULL,
  `erro_geracao`         TEXT           NULL,
  `created_at`           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           TIMESTAMP      NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_mes_ano` (`mes_vigencia`, `ano_vigencia`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`encarte_pai_id`) REFERENCES `encartes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `encarte_itens` (
  `id`                          INT            NOT NULL AUTO_INCREMENT,
  `encarte_id`                  INT            NOT NULL,
  `ordem_exibicao`              SMALLINT       NOT NULL DEFAULT 0,
  `sku`                         VARCHAR(50)    NULL,
  `nome_erp`                    VARCHAR(255)   NULL COMMENT 'Nome bruto vindo do Hub antes da IA',
  `nome_comercial`              VARCHAR(255)   NOT NULL COMMENT 'Nome tratado pelo Gemini',
  `descricao_complementar`      VARCHAR(255)   NULL COMMENT 'Ref, specs, subtitulo extra',
  `preco_normal`                DECIMAL(10,2)  NOT NULL,
  `preco_promocional`           DECIMAL(10,2)  NOT NULL,
  `unidade`                     VARCHAR(10)    NOT NULL DEFAULT 'und',
  `caminho_foto_original`       VARCHAR(255)   NULL,
  `caminho_foto_limpa`          VARCHAR(255)   NOT NULL DEFAULT '',
  `processamento_imagem_status` ENUM('pendente','ok','erro') NOT NULL DEFAULT 'pendente',
  `created_at`                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_encarte` (`encarte_id`),
  FOREIGN KEY (`encarte_id`) REFERENCES `encartes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `config_sistema` (
  `chave`      VARCHAR(100)  NOT NULL,
  `valor`      TEXT          NULL,
  `descricao`  VARCHAR(255)  NULL,
  `updated_at` TIMESTAMP     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `config_sistema` (`chave`, `valor`, `descricao`) VALUES
  ('gemini_api_key',      '',          'Chave API Google Gemini'),
  ('gemini_model',        'gemini-2.5-flash', 'Modelo Gemini utilizado'),
  ('hub_api_url',         'http://localhost/orcador_dev/api/marketing/produto.php', 'Endpoint Hub Precificacao'),
  ('hub_api_token',       '',          'Token Bearer para a API do Hub'),
  ('max_upload_size_mb',  '10',        'Tamanho maximo de upload de imagem em MB'),
  ('watermark_rodape',    'Ofertas validas enquanto durarem os estoques.', 'Texto legal padrao do rodape')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);
