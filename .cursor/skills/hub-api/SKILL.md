---
name: hub-api
description: Referencia para integracao com o endpoint de produtos do Hub de Precificacao (orcador_dev).
---

# Skill: Hub de Precificacao — Endpoint Marketing

## Endpoint em orcador_dev
Arquivo: `orcador_dev/api/marketing/produto.php`

## Contrato
GET /api/marketing/produto.php?sku=25693&token=SEU_TOKEN

Sucesso (200):
```json
{ "status": "success", "data": { "sku": "25693", "nome_erp": "LUMANTI LAMPADA...", "preco_venda_atual": 21.69 } }
```

Nao encontrado (404):
```json
{ "status": "error", "error": "SKU nao localizado" }
```

## Query MySQL (tabela produtos do Hub)
```sql
SELECT codigo_interno AS sku, descricao AS nome_erp, preco_venda AS preco_venda_atual
FROM produtos
WHERE UPPER(TRIM(codigo_interno)) = UPPER(TRIM(?)) AND ativo = 1
LIMIT 1
```

## Token
Validar via header Authorization: Bearer TOKEN ou query param token=TOKEN
Token salvo em config_sistema.hub_api_token no DB da Central de Marketing
