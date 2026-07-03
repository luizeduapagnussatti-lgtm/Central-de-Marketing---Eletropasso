---
name: gemini-marketing
description: Integracao Gemini para normalizacao de nomes e titulos de campanha; Rembg para remocao de fundo offline.
---

# Skill: Gemini + Rembg — Central de Marketing

## Gemini (texto apenas)

Classe: `services/MarketingAssistant.php`  
Modelo padrao: `gemini-2.5-flash`

### Normalizacao de Nome

- System instruction: assistente marketing Eletropasso
- Temperature: **0.1**
- User prompt: nome ERP bruto (somente texto)
- Output: string unica, max 60 chars, sem aspas/markdown
- `thinkingBudget: 0` no 2.5-flash (evita truncamento por tokens de raciocinio)

### Geracao de Titulo de Campanha

- System instruction: copywriter Eletropasso
- Temperature: **0.7**
- User prompt: `Produtos do encarte: ...`
- Output: JSON `{ "opcoes": ["...", "...", "..."] }` via `responseSchema`

## Rembg (imagem — offline)

- Metodo: `removerFundoImagem(string $src, string $dest): bool`
- CLI: `rembg i input.jpg output.png`
- Config: `REMBG_BIN` ou `config_sistema.rembg_bin`
- Em falha: retorna false; UI usa foto original

## Fallbacks

- Sem API key / timeout / erro Gemini: capitalizacao simples ou titulos genericos
- Rembg indisponivel: copia foto original para `limpas/` com status `erro`
