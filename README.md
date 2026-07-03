# Central de Marketing — Eletropasso

Painel interno para **criar, editar e gerenciar encartes promocionais** da Eletropasso (materiais elétricos e iluminação). Integra Hub de Precificação, Google Gemini, Rembg (Python) e Puppeteer (Node.js) para entregar PNGs prontos para redes sociais e impressão.

**Repositório:** [Central-de-Marketing---Eletropasso](https://github.com/luizeduapagnussatti-lgtm/Central-de-Marketing---Eletropasso.git)

---

## Guia para agente Cursor (deploy em produção)

Use esta seção ao clonar o projeto em um servidor novo. **Não há Composer nem framework PHP** — tudo roda em Apache + PHP nativo.

### O que instalar no servidor

| Componente | Versão mínima | Para quê |
|------------|---------------|----------|
| **Apache** (ou Nginx + PHP-FPM) | — | Servir o painel PHP |
| **PHP** | 8.2+ | Painel, API JSON, services |
| **MySQL** | 8.0+ | Banco `central_marketing_eletropasso` |
| **Node.js** | 18+ | Puppeteer (`bin/render_encarte.js`) |
| **Python** | 3.10+ | Rembg CLI (remoção de fundo offline) |
| **npm** | — | Instalar Puppeteer na raiz do projeto |

### Extensões PHP obrigatórias

`pdo_mysql`, `curl`, `json`, `mbstring`, `fileinfo`

### Funções PHP que não podem estar desabilitadas

`exec()` — usada por **Puppeteer** (`EncarteRenderService`) e **Rembg** (`MarketingAssistant`). Se `disable_functions` bloquear `exec`, geração de PNG e limpeza de fundo falham.

### Comandos pós-clone (ordem)

```bash
# 1. Banco — aplicar migrations na ordem (ver seção SQL)
mysql -u USUARIO -p < sql/001_schema_inicial.sql
mysql -u USUARIO -p < sql/002_config_rembg.sql
mysql -u USUARIO -p < sql/003_modelo_02.sql
mysql -u USUARIO -p < sql/004_modelos_config_visual.sql
mysql -u USUARIO -p < sql/005_modelo_03.sql
mysql -u USUARIO -p < sql/006_modelo_fundos.sql
mysql -u USUARIO -p < sql/007_fabric_state.sql

# 2. Ambiente
cp .env.example .env   # Windows: copy .env.example .env

# 3. Node (Puppeteer — obrigatório para gerar PNG)
npm install
npm approve-scripts puppeteer    # se o npm pedir aprovação de scripts
node node_modules/puppeteer/install.mjs

# 4. Rembg (remoção de fundo)
pip install "rembg[cli]"
rembg --help

# 5. (Opcional) Rebuild CSS Tailwind dos encartes — só se alterou templates
npm run build:encarte-css
```

### Variáveis `.env` críticas em produção

| Variável | Produção |
|----------|----------|
| `APP_BASE_URL` | URL pública do painel (com barra final) |
| `MARKETING_DB_*` | Credenciais MySQL do servidor |
| `NODE_BIN` | Caminho absoluto do `node` para o usuário do Apache (ex.: `/usr/bin/node`) |
| `REMBG_BIN` | `rembg` ou caminho completo do executável Python |
| `GEMINI_API_KEY` | Chave Google AI (texto: nomes e títulos) |
| `GEMINI_MODEL` | `gemini-2.5-flash` |
| `HUB_API_URL` | URL do endpoint no Hub de Precificação |
| `HUB_API_TOKEN` | Mesmo token configurado no `orcador_dev` |

### Pastas com permissão de escrita (usuário do Apache)

`temp/`, `encartes/gerados/`, `assets/produtos/originais/`, `assets/produtos/limpas/`, `assets/modelos/fundos/`, `assets/modelos/elementos/`, `storage/logs/`

### Testes de fumaça após deploy

```bash
php scripts/test_gemini.php
php scripts/test_rembg.php
php scripts/test_render_png.php
php scripts/test_encarte_save.php
```

### Dependências de rede

| Recurso | Rede |
|---------|------|
| Gemini API | Internet (HTTPS outbound) |
| Hub de Precificação | Rede interna / mesma infra |
| Editor visual (`editar-modelo.php`) | CDN jsDelivr (Fabric.js 5) e cdn.tailwindcss.com — **requer internet no browser do operador** |
| Templates de encarte (render PNG) | **Sem CDN** — CSS/fonts locais em `assets/` |

### Documentação auxiliar para o agente

- Regras Cursor: `.cursor/rules/` (stack, PHP, templates, Puppeteer, integrações)
- Skills: `.cursor/skills/encarte-modelo`, `hub-api`, `gemini-marketing`
- Produto/design: `PRODUCT.md`, `DESIGN.md`

---

## Sumário

- [O que o sistema faz](#o-que-o-sistema-faz)
- [Arquitetura](#arquitetura)
- [Stack e bibliotecas](#stack-e-bibliotecas)
- [Pré-requisitos](#pré-requisitos)
- [Instalação (desenvolvimento)](#instalação-desenvolvimento)
- [Migrations SQL](#migrations-sql)
- [Configuração](#configuração)
- [Uso do painel](#uso-do-painel)
- [Modelos de encarte](#modelos-de-encarte)
- [Formatos de saída](#formatos-de-saída)
- [API interna](#api-interna)
- [Integrações externas](#integrações-externas)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Scripts utilitários](#scripts-utilitários)
- [Desenvolvimento com Cursor](#desenvolvimento-com-cursor)
- [Deploy e checklist](#deploy-e-checklist)
- [Solução de problemas](#solução-de-problemas)

---

## O que o sistema faz

| Etapa | Quem faz | Resultado |
|-------|----------|-----------|
| Escolher modelo visual | Operador | Layout do encarte (`modelo_01`, `modelo_02`, `modelo_03`) |
| Buscar produto por SKU | Hub de Precificação | Nome ERP + preço de venda |
| Normalizar nome | Gemini (opcional) | Nome comercial legível |
| Definir preço promocional | Operador | Preço "Por" no cartão |
| Enviar foto do produto | Operador | Upload; **Rembg** remove fundo offline |
| Escolher título da campanha | Operador + Gemini | Título do encarte |
| Gerar PNG | Puppeteer (Node.js) | Arquivo final em alta resolução |
| Personalizar modelo (admin) | Editor Fabric.js | Cores, textos, fundos, overlay visual |
| Histórico | MySQL | Encartes agrupados por mês/ano |

**Premissa de design:** o encarte final **não** é screenshot do browser do usuário. O backend monta HTML/CSS fixo e o Puppeteer captura o PNG de forma pixel-perfect.

**Identidade visual:** vermelho Eletropasso (`#b91c1c`), badges **OFERTA** / **PROMOÇÃO**, logo em `assets/brand/logo_eletropasso_preta.png`.

---

## Arquitetura

```text
Operador (navegador)
    │
    ├─► index.php / modelos.php / criar.php / config.php / gerenciar-modelos.php / editar-modelo.php
    │
    ├─► api/index.php?recurso={encarte|config|modelo}&acao={acao}
    │       ├─ EncarteService           → MySQL (encartes, itens)
    │       ├─ ModeloLayoutService      → MySQL (modelos_layout, config_visual, fabric_state)
    │       ├─ HubPrecificacaoService   → orcador_dev API
    │       ├─ MarketingAssistant       → Gemini API (texto) + Rembg CLI (imagem)
    │       └─ EncarteRenderService     → bin/render_encarte.js (Puppeteer)
    │
    ├─► views/templates/modelo_0N.php   → HTML/CSS do encarte (Puppeteer lê daqui)
    │
    └─► encartes/gerados/*.png          → saída final
```

---

## Stack e bibliotecas

### Runtime (servidor)

| Camada | Tecnologia | Observação |
|--------|------------|------------|
| Painel e API | PHP 8.2+, Vanilla JS, HTML5/CSS3 | Sem Laravel/Symfony |
| Banco | MySQL 8 | JSON em `config_visual`, `formatos_suportados` |
| Render PNG | Node.js 18+ + **Puppeteer** ^24 | `bin/render_encarte.js` |
| IA (texto) | **Google Gemini** `gemini-2.5-flash` | Nomes e títulos; `thinkingBudget: 0` |
| Remoção de fundo | **Rembg** (Python CLI) | Offline; `pip install rembg[cli]` |
| Catálogo/preço | Hub de Precificação | `orcador_dev` |

### npm (`package.json`)

| Pacote | Tipo | Uso |
|--------|------|-----|
| `puppeteer` | dependency | Render PNG headless |
| `tailwindcss` + `@tailwindcss/cli` | devDependency | Build CSS dos encartes (`npm run build:encarte-css`) |

O CSS compilado `assets/encarte/tailwind.encarte.css` **já vem no repositório**. Em produção, `npm install` é obrigatório por causa do Puppeteer; o rebuild Tailwind só é necessário se templates forem alterados.

### Frontend dos encartes (assets versionados)

| Recurso | Caminho | Uso |
|---------|---------|-----|
| Tailwind encarte (prefixo `ep-`) | `assets/encarte/tailwind.encarte.css` | Layout utilitário no `modelo_03` |
| Utilitários varejo | `assets/encarte/encarte-utilities.css` | Efeitos (.preco-3d, .foto-flutuante, etc.) |
| Fontes locais | `assets/fonts/` (Bebas Neue, Oswald) | Títulos premium — sem `@import` externo no render |
| Font Awesome subset | `assets/vendor/fontawesome/` | Ícones opcionais nos encartes |
| Fundos de modelo | `assets/modelos/fundos/` | PNGs de palco (ex.: modelo_03) |
| Previews | `assets/modelos/modelo_0N.png` | Galeria de seleção |

### Editor visual de modelos (somente painel admin)

| Biblioteca | Origem | Página |
|------------|--------|--------|
| **Fabric.js 5** | CDN jsDelivr | `editar-modelo.php` |
| **Tailwind CSS** | CDN (play) | `editar-modelo.php` |

O estado do canvas Fabric é salvo em `modelos_layout.config_visual.fabric_state` e convertido para HTML/CSS absoluto no render Puppeteer (`ep_render_fabric_object()` em `config/app.php`).

### PHP — sem Composer

Todas as classes em `services/`, `controllers/`, `config/`. Autoload manual via `require_once` no bootstrap.

---

## Pré-requisitos

### Desenvolvimento (XAMPP)

- **XAMPP** com Apache e MySQL ativos
- **PHP 8.2+** com extensões listadas acima; `exec()` habilitado
- **Node.js 18+** no PATH (ou `NODE_BIN` com caminho absoluto)
- **Python 3.10+** com Rembg
- Acesso ao **Hub de Precificação** (`orcador_dev`) na mesma rede
- **Composer não é necessário**

### Produção

Mesmos requisitos de runtime. Configure `NODE_BIN` e `REMBG_BIN` para o usuário que executa o PHP (Apache/`www-data`). Em Linux, instale dependências do Chromium para Puppeteer (bibliotecas GTK/NSPR — ver [documentação Puppeteer](https://pptr.dev/troubleshooting)).

---

## Instalação (desenvolvimento)

### 1. Clonar

```bash
git clone https://github.com/luizeduapagnussatti-lgtm/Central-de-Marketing---Eletropasso.git
cd Central-de-Marketing---Eletropasso
```

Ambiente local típico: `c:\xampp\htdocs\Central de marketing_dev\`

### 2. Banco de dados

Ver [Migrations SQL](#migrations-sql).

### 3. Variáveis de ambiente

```bash
copy .env.example .env
```

Edite `.env` (MySQL, Gemini, Hub, Node, Rembg).

### 4. Node.js e Puppeteer

```bash
npm install
npm approve-scripts puppeteer
node node_modules/puppeteer/install.mjs
```

### 5. Rembg

```bash
pip install "rembg[cli]"
```

No `.env`: `REMBG_BIN=rembg` (ou caminho completo no Windows, ex.: `C:\Python312\Scripts\rembg.exe`).

### 6. Permissões de pasta

Garanta escrita em: `temp/`, `encartes/gerados/`, `assets/produtos/originais/`, `assets/produtos/limpas/`, `assets/modelos/fundos/`, `assets/modelos/elementos/`, `storage/logs/`.

### 7. Tailwind encarte (opcional em dev)

```bash
npm run build:encarte-css
```

Detalhes: `assets/encarte/README.md`

### 8. Impeccable (opcional — design no Cursor)

```bash
npx impeccable install
```

No Cursor Chat: `/impeccable init`

---

## Migrations SQL

Aplicar **nesta ordem** em banco novo ou ao atualizar produção:

| Arquivo | Conteúdo |
|---------|----------|
| `sql/001_schema_inicial.sql` | Schema completo, tabelas, `modelo_01` e `modelo_02` |
| `sql/002_config_rembg.sql` | Chave `rembg_bin`; modelo Gemini 2.5 |
| `sql/003_modelo_02.sql` | Metadados do modelo 02 (reexecutável) |
| `sql/004_modelos_config_visual.sql` | Colunas `descricao`, `config_visual`, defaults visuais |
| `sql/005_modelo_03.sql` | Insere `modelo_03` Premium Escuro |
| `sql/006_modelo_fundos.sql` | Fundos estáticos do modelo_03 |
| `sql/007_fabric_state.sql` | Inicializa `fabric_state` vazio no JSON |

```bash
mysql -u root -p central_marketing_eletropasso < sql/001_schema_inicial.sql
# ... repetir para 002–007
```

Migrations 003–007 são **reexecutáveis** (INSERT … ON DUPLICATE KEY / IF NOT EXISTS).

---

## Configuração

### Arquivo `.env`

| Variável | Descrição |
|----------|-----------|
| `APP_ENV` | `development` ou `production` |
| `APP_BASE_URL` | URL base com barra final |
| `MARKETING_DB_*` | Host, porta, nome, usuário, senha, charset |
| `GEMINI_API_KEY` | API Google Gemini |
| `GEMINI_MODEL` | Padrão: `gemini-2.5-flash` |
| `REMBG_BIN` | Executável Rembg |
| `HUB_API_URL` | Endpoint produto no Hub |
| `HUB_API_TOKEN` | Token Bearer |
| `NODE_BIN` | Caminho do `node.exe` / `node` |
| `PUPPETEER_TIMEOUT_MS` | Timeout render (padrão 30000) |
| `MAX_UPLOAD_SIZE_MB` | Upload de fotos |

> **Nunca commite o `.env`**

### Tela de configurações

`config.php` — valores persistidos em `config_sistema` (prioridade sobre `.env` quando o banco responde).

### Sincronizar Gemini `.env` → banco

```bash
php scripts/sync_gemini_config.php
```

### Hub de Precificação

No `.env` do **orcador_dev**:

```env
MARKETING_API_TOKEN=seu_token_secreto
```

Use o **mesmo valor** em `HUB_API_TOKEN` na Central de Marketing.

---

## Uso do painel

| Página | URL | Função |
|--------|-----|--------|
| Galeria | `index.php` | Encartes por mês; download, editar, nova versão |
| Escolher modelo | `modelos.php` | Seleção visual antes de criar encarte |
| Criar / Editar | `criar.php?modelo=` ou `?id=` | Formulário, SKU, IA, geração PNG |
| Configurações | `config.php` | API keys, Hub, rodapé, checklist deploy |
| Gerenciar modelos | `gerenciar-modelos.php` | Ativar/desativar modelos, links para editor |
| Editor visual | `editar-modelo.php?id=` | Fabric.js — cores, textos, fundos, overlay |

### Fluxo do operador

1. **Galeria** → **+ Novo Encarte** → `modelos.php`
2. Escolher **modelo visual** → `criar.php?modelo=modelo_0N`
3. Configurar campanha, **SKU → Buscar** (Hub), upload de fotos
4. (Opcional) **Títulos com IA** / normalização de nome
5. **Salvar rascunho** ou **Gerar Encarte PNG**
6. Baixar na **Galeria**

### Fluxo admin (modelos)

1. **Modelos** (nav) → `gerenciar-modelos.php`
2. **Editar visual** → `editar-modelo.php` (Fabric.js)
3. Salvar — persiste `config_visual` + `fabric_state` via API `modelo/salvar`

---

## Modelos de encarte

| Código | Nome | Formatos | Max itens | Template |
|--------|------|----------|-----------|----------|
| `modelo_01` | Grade Flutuante Eletropasso | 9x16, status, 1x1, 16x9, A4 | 12 | `views/templates/modelo_01.php` |
| `modelo_02` | Estilo Livre — Feed Quadrado | 1x1 | 5 | `views/templates/modelo_02.php` |
| `modelo_03` | Premium Escuro — Grid 3x2 | 9x16, status | 6 | `views/templates/modelo_03.php` |

Cada modelo possui `config_visual` (JSON): cores, textos, ícones, fundos, `fabric_state`. Modelos **inativos** não aparecem em `modelos.php`, mas encartes existentes continuam renderizando.

---

## Formatos de saída

| Código | Dimensão (px) | Scale | Uso |
|--------|---------------|-------|-----|
| `9x16` | 1080 × 1920 | 2× | Stories Instagram / WhatsApp |
| `status` | 1080 × 1920 | 2× | Status WhatsApp |
| `1x1` | 1080 × 1080 | 2× | Feed quadrado |
| `16x9` | 1920 × 1080 | 2× | Paisagem |
| `a4` | 2480 × 3508 | 1× | Impressão A4 |

Viewports definidos em `bin/render_encarte.js` e `.cursor/rules/04-puppeteer-render.mdc`.

---

## API interna

Base: `api/index.php?recurso={recurso}&acao={acao}`

Resposta padrão:

```json
{ "success": true, "data": {}, "error": null }
```

### Encarte (`recurso=encarte`)

| Ação | Método | Descrição |
|------|--------|-----------|
| `listar` | GET | Galeria agrupada por mês/ano |
| `detalhe` | GET | `?id=` — encarte + itens |
| `salvar` | POST | JSON — criar/atualizar rascunho |
| `gerar` | POST | JSON `{ "id": N }` — render PNG |
| `status` | GET | `?id=` — status da geração |
| `nova_versao` | POST | Duplica encarte (V2+) |
| `upload_foto` | POST | multipart — upload + Rembg |
| `buscar_sku` | GET | `?sku=` — Hub + Gemini |
| `titulos_ia` | POST | JSON `{ "nomes": [] }` |
| `normalizar` | POST | JSON `{ "nome_erp": "..." }` |

### Config (`recurso=config`)

| Ação | Método | Descrição |
|------|--------|-----------|
| `listar` | GET | Chaves de `config_sistema` |
| `salvar` | POST | JSON — configurações permitidas |

### Modelo (`recurso=modelo`)

| Ação | Método | Descrição |
|------|--------|-----------|
| `listar` | GET | Todos os modelos (admin) |
| `detalhe` | GET | `?id=` — modelo + config merged |
| `salvar` | POST | JSON — config_visual, fabric_state, metadados |
| `alternar_ativo` | POST | Ativa/desativa modelo |
| `excluir` | POST | Remove modelo |
| `gerar_preview` | POST | Preview PNG via Puppeteer |
| `upload_fundo` | POST | multipart — fundo por formato |
| `upload_elemento` | POST | multipart — imagem para canvas Fabric |

---

## Integrações externas

### Gemini (texto)

Classe: `services/MarketingAssistant.php` · Modelo: **`gemini-2.5-flash`**

| Função | Temperature | Detalhe |
|--------|-------------|---------|
| Normalizar nome | 0.1 | max 60 chars; `thinkingBudget: 0` |
| Títulos de campanha | 0.7 | JSON `{ "opcoes": [...] }`; `thinkingBudget: 0` |

Fallback sem chave/erro: capitalização simples ou títulos genéricos.

### Rembg (imagem offline)

```bash
rembg i entrada.jpg saida.png
```

Config: `REMBG_BIN` · Saída: `assets/produtos/limpas/*.png` · Fallback: foto original, status `erro`.

### Hub de Precificação

```http
GET /orcador_dev/api/marketing/produto.php?sku=25693&token=SEU_TOKEN
```

Service: `services/HubPrecificacaoService.php` (timeout 5s).

Detalhes: `.cursor/rules/05-integracoes-apis.mdc`, `.cursor/skills/gemini-marketing/SKILL.md`.

---

## Estrutura do projeto

```text
Central de marketing_dev/
├── api/index.php              # Router JSON (encarte, config, modelo)
├── assets/
│   ├── brand/                 # logos, tokens.css
│   ├── encarte/               # tailwind.encarte.css, fonts.css, utilities
│   ├── fonts/                 # Bebas Neue, Oswald (woff2)
│   ├── modelos/               # previews PNG, fundos/, elementos/
│   ├── produtos/              # originais/, limpas/
│   └── vendor/fontawesome/    # subset FA + webfonts
├── bin/
│   ├── render_encarte.js      # Puppeteer — render PNG
│   ├── split_logo.js          # utilitário logos
│   └── process_logos.js
├── config/                    # bootstrap, app.php, database.php
├── controllers/               # encarte, config, modelo
├── encartes/gerados/          # PNGs finais
├── public/css|js/             # painel admin (vanilla)
├── scripts/                   # testes e utilitários CLI
├── services/                  # EncarteService, ModeloLayoutService, etc.
├── sql/                       # 001–007 migrations
├── temp/                      # HTML temp para Puppeteer
├── views/
│   ├── partials/              # app_header.php
│   └── templates/             # modelo_01.php … modelo_03.php
├── .cursor/rules|skills/      # regras e skills Cursor
├── index.php                  # galeria
├── modelos.php                # seleção de modelo
├── criar.php                  # formulário encarte
├── gerenciar-modelos.php      # admin modelos
├── editar-modelo.php          # editor Fabric.js
├── config.php
├── package.json               # puppeteer + tailwind (build encarte)
├── tailwind.encarte.config.js
├── .env                       # secrets (não commitar)
├── PRODUCT.md
└── DESIGN.md
```

---

## Scripts utilitários

| Script | Uso |
|--------|-----|
| `php scripts/sync_gemini_config.php` | Sync `.env` → banco (Gemini + Rembg) |
| `php scripts/limpar_temp.php` | Limpa HTML antigo em `temp/` |
| `php scripts/test_encarte_save.php` | Teste persistência MySQL |
| `php scripts/test_gemini.php` | Teste Gemini (nome + títulos) |
| `php scripts/test_rembg.php` | Teste Rembg CLI |
| `php scripts/test_render_png.php` | Teste PHP → Puppeteer → PNG |
| `php scripts/gerar_preview_modelo.php` | Gera preview PNG de modelo |
| `php scripts/importar_logos.php` | Importa logos do workspace Cursor |
| `php scripts/run_007_fabric_state.php` | Aplica migration 007 via PDO |
| `npm run build:encarte-css` | Recompila Tailwind dos encartes |
| `node bin/render_encarte.js <html> <formato> <out.png>` | Render manual |

---

## Desenvolvimento com Cursor

### Rules (`.cursor/rules/`)

| Arquivo | Escopo |
|---------|--------|
| `01-projeto-global.mdc` | Stack, identidade, JSON API |
| `02-php-arquitetura.mdc` | MVC leve, PDO, services |
| `03-encarte-templates.mdc` | Templates HTML/CSS Puppeteer |
| `04-puppeteer-render.mdc` | Viewports, screenshot |
| `05-integracoes-apis.mdc` | Hub, Gemini, Rembg |

### Skills custom

- `encarte-modelo` — checklist visual dos cartões
- `hub-api` — contrato Hub no orcador_dev
- `gemini-marketing` — Gemini + Rembg

### Documentação

- `PRODUCT.md` — usuários, propósito, princípios
- `DESIGN.md` — tokens, componentes, do's/don'ts
- `.impeccable.md` — contexto Impeccable

---

## Deploy e checklist

Antes de colocar em produção na rede interna:

- [ ] MySQL: banco criado; migrations **001–007** aplicadas
- [ ] `.env` configurado (sem commitar); `APP_BASE_URL` de produção
- [ ] PHP 8.2+ com extensões e **`exec()` habilitado**
- [ ] Node.js instalado; `NODE_BIN` correto para o usuário do Apache
- [ ] `npm install` + Chromium Puppeteer baixado (`install.mjs`)
- [ ] Python + `pip install "rembg[cli]"`; `REMBG_BIN` testado
- [ ] Permissões de escrita: temp, encartes, produtos, modelos/fundos, modelos/elementos, logs
- [ ] `GEMINI_API_KEY` + modelo `gemini-2.5-flash`
- [ ] Hub API ativa; tokens sincronizados (`HUB_API_TOKEN` ↔ `MARKETING_API_TOKEN`)
- [ ] Logos em `assets/brand/` (preta para header claro, branca para encartes escuros)
- [ ] Testes: `test_gemini.php`, `test_rembg.php`, `test_render_png.php`
- [ ] Testar geração nos formatos usados por cada modelo ativo
- [ ] (Se alterou templates) `npm run build:encarte-css`

Checklist também em **Configurações** (`config.php`).

---

## Solução de problemas

### `node` / `npm` não reconhecido

Adicione Node ao PATH ou defina `NODE_BIN` com caminho absoluto no `.env`.

### Puppeteer: `'C:\Program' não é reconhecido`

```env
NODE_BIN=C:\Program Files\nodejs\node.exe
```

`EncarteRenderService` escapa o caminho automaticamente.

### Puppeteer falha no Linux (Chrome dependencies)

Instale pacotes do Chromium listados na [troubleshooting Puppeteer](https://pptr.dev/troubleshooting). Rode como usuário do Apache: `node bin/render_encarte.js ...`

### `exec()` has been disabled

Remova `exec` de `disable_functions` no `php.ini`. Necessário para Puppeteer e Rembg.

### MySQL recusou conexão

Verifique serviço MySQL e credenciais em `.env`.

### Gemini retorna fallback

Verifique `GEMINI_API_KEY`. Rode `php scripts/sync_gemini_config.php` e `php scripts/test_gemini.php`. Modelo correto: **`gemini-2.5-flash`** (2.0 descontinuado).

### Nome normalizado truncado

Gemini 2.5 Flash usa tokens de "thinking". O projeto define `thinkingBudget: 0` em `MarketingAssistant.php`.

### Hub retorna "SKU não localizado"

Confirme produto ativo no `orcador_dev` e tokens/URL corretos.

### Rembg falha no upload

`pip install "rembg[cli]"` · teste: `rembg i entrada.jpg saida.png` · ajuste `REMBG_BIN`.

### Encarte sem estilos Tailwind (modelo_03)

Confirme que `assets/encarte/tailwind.encarte.css` existe. Se faltou, rode `npm run build:encarte-css`.

### Editor visual não carrega

`editar-modelo.php` depende de CDN (Fabric.js, Tailwind play). Verifique internet no browser do operador.

---

## Licença

Ver arquivo [LICENSE](LICENSE).
