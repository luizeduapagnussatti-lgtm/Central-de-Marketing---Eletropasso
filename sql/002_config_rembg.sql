-- sqlfluff:dialect:mysql
-- Central de Marketing — config Rembg e modelo Gemini 2.0
USE `central_marketing_eletropasso`;

INSERT INTO `config_sistema` (`chave`, `valor`, `descricao`) VALUES
  ('rembg_bin', 'rembg', 'Executavel Rembg CLI para remocao de fundo (ex: rembg ou C:\\Python312\\Scripts\\rembg.exe')
ON DUPLICATE KEY UPDATE `descricao` = VALUES(`descricao`);

UPDATE `config_sistema` SET `valor` = 'gemini-2.5-flash' WHERE `chave` = 'gemini_model' AND (`valor` IS NULL OR `valor` = '' OR `valor` = 'gemini-2.0-flash');
