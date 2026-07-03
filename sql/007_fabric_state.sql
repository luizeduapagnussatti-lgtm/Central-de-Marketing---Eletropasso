-- sqlfluff:dialect:mysql
-- Migration 007 — fabric_state vazio em config_visual (editor Fabric.js)

USE `central_marketing_eletropasso`;

UPDATE `modelos_layout`
SET config_visual = JSON_SET(
  COALESCE(config_visual, JSON_OBJECT()),
  '$.fabric_state', JSON_OBJECT('version', '5.3.0', 'objects', JSON_ARRAY())
)
WHERE JSON_EXTRACT(`config_visual`, '$.fabric_state') IS NULL;
