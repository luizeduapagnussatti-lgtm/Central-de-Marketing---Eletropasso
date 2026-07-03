# Product

## Register

product

## Users

Equipe de marketing e vendas da Eletropasso (materiais elétricos e iluminação). Usam a ferramenta no escritório ou na loja, em desktop, para montar encartes promocionais rapidamente a partir de SKUs do Hub de Precificação. Precisam de fluxo direto: buscar produto, ajustar preço/nome, gerar PNG nos formatos de rede social e impressão.

## Product Purpose

Central de Marketing é o painel interno para criar, editar e gerenciar encartes promocionais da Eletropasso. Integra Hub de Precificação (SKU/preço), Gemini (normalização de nomes, títulos, remoção de fundo) e Puppeteer (render PNG). Sucesso = encarte gerado no formato certo, com identidade visual Eletropasso, em poucos cliques, sem depender de designer externo.

## Brand Personality

Direto, confiável, comercial. Três palavras: **prático**, **forte**, **claro**. A interface do painel deve transmitir eficiência de ferramenta interna; os encartes gerados carregam a voz promocional vermelha e assertiva da marca.

## Anti-references

- Gradientes genéricos, glassmorphism decorativo, estética "AI slop" (Inter + cream background + card grid idêntico).
- Layout que depende de JavaScript para posicionamento nos templates de encarte (Puppeteer exige CSS puro).
- Tipografia display exagerada no painel admin (hero SaaS, métricas gigantes).
- Paletas frias ou pastéis que diluam o vermelho Eletropasso (#b91c1c).
- Side-stripe borders, gradient text, eyebrows em caixa alta em toda seção.

## Design Principles

1. **Identidade preservada** — vermelho Eletropasso, logo e badges "OFERTA"/"PROMOÇÃO" são não negociáveis nos encartes; no painel, usados com moderação como acento.
2. **Ferramenta antes de vitrine** — o painel prioriza legibilidade, formulários claros e feedback de estado (rascunho, gerando, concluído, erro).
3. **Render previsível** — o que aparece no preview deve ser o que o Puppeteer captura; sem animações ou fontes externas nos templates.
4. **Contraste legível** — texto de corpo ≥4.5:1; placeholders e metadados não podem sumir em cinza claro.
5. **Responsivo onde importa** — painel usável em telas menores; encartes com dimensões fixas por formato.

## Accessibility & Inclusion

Alvo WCAG 2.1 AA no painel administrativo. Respeitar `prefers-reduced-motion` em qualquer animação do painel (loader, transições). Status não depende só de cor (badges têm texto). Formulários com labels explícitos e mensagens de erro claras.
