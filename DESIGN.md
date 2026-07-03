---
name: Central de Marketing Eletropasso
description: Painel interno e templates de encarte com identidade vermelha Eletropasso
colors:
  primary: "#b91c1c"
  primary-dark: "#7f1d1d"
  border: "#fecaca"
  surface: "#ffffff"
  background: "#f9fafb"
  ink: "#111827"
  muted: "#6b7280"
  badge-bg: "#b91c1c"
  badge-text: "#ffffff"
  success: "#065f46"
  success-bg: "#d1fae5"
  warning: "#92400e"
  warning-bg: "#fef3c7"
  error: "#991b1b"
  error-bg: "#fee2e2"
typography:
  display:
    fontFamily: "Arial, Helvetica, sans-serif"
    fontSize: "22px"
    fontWeight: 800
    lineHeight: 1.2
    letterSpacing: "normal"
  headline:
    fontFamily: "Arial, Helvetica, sans-serif"
    fontSize: "18px"
    fontWeight: 800
    lineHeight: 1.3
    letterSpacing: "normal"
  body:
    fontFamily: "Arial, Helvetica, sans-serif"
    fontSize: "14px"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Arial, Helvetica, sans-serif"
    fontSize: "13px"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "normal"
  caption:
    fontFamily: "Arial, Helvetica, sans-serif"
    fontSize: "12px"
    fontWeight: 400
    lineHeight: 1.4
    letterSpacing: "normal"
rounded:
  sm: "6px"
  md: "8px"
  lg: "12px"
  pill: "999px"
spacing:
  xs: "6px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.md}"
    padding: "10px 18px"
  button-primary-hover:
    backgroundColor: "{colors.primary-dark}"
    textColor: "#ffffff"
    rounded: "{rounded.md}"
    padding: "10px 18px"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.primary-dark}"
    rounded: "{rounded.md}"
    padding: "10px 18px"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "14px"
  form-panel:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "24px"
  badge-oferta:
    backgroundColor: "{colors.badge-bg}"
    textColor: "{colors.badge-text}"
    rounded: "{rounded.sm}"
    padding: "2px 8px"
---

## Overview

Sistema visual em duas camadas: **painel admin** (`public/css/app.css`, páginas PHP) e **templates de encarte** (`views/templates/`, CSS inline). Ambos compartilham tokens `--ep-*` definidos em `assets/brand/tokens.css`. Layout do painel: header fixo com logo + nav, main centralizado (max 1200px), cards brancos com borda `#fecaca`. Encartes: dimensões fixas por formato, grid de produtos, tipografia Arial, vermelho como acento promocional.

## Colors

| Token | Hex | Uso |
|-------|-----|-----|
| `--ep-primary` | `#b91c1c` | Botões primários, links, preço promocional, badge OFERTA |
| `--ep-dark` | `#7f1d1d` | Títulos, hover de botão primário |
| `--ep-border` | `#fecaca` | Bordas de cards, header, divisores |
| `--ep-bg` / surface | `#ffffff` | Fundo de cards e header |
| `--ep-light` | `#f9fafb` | Fundo da página, linhas de item |
| `--ep-gray` | `#6b7280` | Metadados, placeholders, captions |
| Ink | `#111827` | Texto principal |

Status badges usam pares semânticos (amarelo rascunho, azul gerando, verde concluído, vermelho erro). Sem gradientes. Estratégia de cor: **restrained** no painel (neutros + acento vermelho ≤10%); **committed** nos encartes (vermelho em badges e preços).

## Typography

Família única: **Arial, Helvetica, sans-serif** — legível, neutra, segura para render Puppeteer sem `@import` externo.

| Elemento | Tamanho | Peso |
|----------|---------|------|
| Header h1 | 22px | 800 |
| Section h2 | 18px | 800 (dark) |
| Card title h3 | 15px | 700 |
| Body / inputs | 14px | 400–600 |
| Meta / badge | 11–12px | 400–700 |

Preço promocional em encarte: ≥2× do preço normal, peso 800, cor primary. Nomes longos: ellipsis com overflow hidden.

## Elevation

Superfície predominantemente **flat**. Hierarquia por fundo branco sobre `#f9fafb`, bordas 1–2px `#fecaca`, sem box-shadow no painel (exceto overlays). Loader e lightbox: overlay escuro `rgba(0,0,0,0.55–0.85)`, z-index modal (loader 9999, lightbox 10000 — candidatos a refatorar para escala semântica).

## Components

- **`.app-header`** — flex, logo 48px, nav com `.btn`
- **`.btn` / `.btn-primary` / `.btn-secondary` / `.btn-danger` / `.btn-sm`** — inline-flex, radius 8px, transição background 0.15s
- **`.encarte-card`** — grid thumb 9:16 + body + actions
- **`.form-panel` + `.form-grid` + `.form-field`** — formulários responsivos
- **`.item-row`** — drag-and-drop de produtos, grid 4 colunas
- **`.status-badge`** — pill uppercase por estado
- **`.loader-overlay` / `.lightbox`** — modais fullscreen
- **Encarte cartão** — foto, SKU, nome, preço riscado, preço destaque, badge OFERTA/PROMOÇÃO

## Do's and Don'ts

**Do**
- Usar tokens `--ep-*` em CSS novo; referenciar `assets/brand/tokens.css` quando possível.
- Manter contraste AA; testar formulários e badges de status.
- Preservar logo `assets/brand/logo_eletropasso.png` no header e encartes.
- CSS puro nos templates; dimensões fixas por formato (9x16, status, 1x1, 16x9, A4).

**Don't**
- `@import` de fontes externas nos templates.
- `animation` / `transition` nos encartes (quebra screenshot).
- `position: fixed` nos templates.
- Gradientes decorativos, glassmorphism, gradient text.
- Inventar nova paleta que substitua o vermelho Eletropasso.
