---
name: encarte-modelo
description: Use ao criar ou modificar templates HTML/CSS de encarte em views/templates/.
---

# Skill: Encarte Modelo Eletropasso

Use ao criar ou modificar templates HTML/CSS em views/templates/.

## Arquitetura de camadas (overlay)

1. **Palco estatico** — PNG/JPEG em `assets/modelos/fundos/{codigo}_{formato}.png`, referenciado em `config_visual.fundos`. Usar `background-image` no `.encarte-container` via `marketing_encarte_fundo_src()`.
2. **Camada dinamica** — HTML posiciona cards, fotos, precos e rodape legal por cima do palco.

Nao desenhar raios, gradientes complexos ou logo fixa em CSS quando o palco ja contem a arte.

## Checklist Visual do Cartao de Produto

- [ ] Foto centralizada, fundo transparente ou branco
- [ ] Nome em no maximo 2 linhas (ellipsis para overflow)
- [ ] Preco normal: fonte pequena, riscado, cor cinza
- [ ] Preco promo: fonte >= 2x, negrito, cor branca em badge vermelho EP
- [ ] Badge "OFERTA" / "PROMOCAO" no canto superior direito do cartao
- [ ] Unidade (und/cx/m) abaixo do preco
- [ ] SKU discreto (6-8px, cinza claro) acima do nome — opcional
- [ ] Sem JS de layout; todo CSS puro Grid/Flexbox

## Assets compartilhados

- `assets/encarte/encarte-utilities.css` — `.foto-flutuante`, `.preco-3d`, `.card-glow`
- `assets/encarte/fonts.css` — Oswald, Bebas Neue (locais, offline)
- `assets/vendor/fontawesome/fontawesome-subset.css` — opcional, toggle `icones.tipo`
- `assets/encarte/tailwind.encarte.css` — utilitarios prefixados `ep-` (opcional)

Incluir no `<head>` via `marketing_encarte_stylesheet_url($base_path, '...')`.

## Variaveis CSS disponiveis

--ep-primary, --ep-dark, --ep-border, --ep-bg, --ep-badge-bg, --ep-badge-txt

## Foto PNG sem fundo (rembg)

- Priorizar `caminho_foto_limpa` do item — injetar direto em `<img src="...">` via `marketing_encarte_foto_src()`
- Nao usar canvas nem background-image na **foto do produto**
- Efeito flutuante: classe `.foto-flutuante` ou `.foto-flutuante--forte` na tag **img** (nao no container)
- O drop-shadow ignora pixels transparentes do PNG e projeta sombra apenas no contorno visivel do produto

## Fontes Locais

Carregar de `assets/fonts/` via `assets/encarte/fonts.css` — nao usar Google Fonts CDN (quebra no Puppeteer offline)
