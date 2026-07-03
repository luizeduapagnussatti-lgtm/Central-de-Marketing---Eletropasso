-- sqlfluff:dialect:mysql
-- Migration 006 — config_visual.fundos (palco estatico por formato)

USE `central_marketing_eletropasso`;

UPDATE `modelos_layout`
SET `config_visual` = JSON_SET(
  COALESCE(`config_visual`, JSON_OBJECT()),
  '$.fundos',
  JSON_OBJECT(
    '9x16', 'assets/modelos/fundos/modelo_03_9x16.png',
    'status', 'assets/modelos/fundos/modelo_03_9x16.png'
  )
)
WHERE `codigo` = 'modelo_03';
