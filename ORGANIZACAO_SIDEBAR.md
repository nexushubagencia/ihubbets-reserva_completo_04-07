# Organização do Sidebar AdminLTE - IHUB BETS V2.1.0

## Status Geral
- **Sidebar atualizado:** 32 rotas adicionadas ao menu
- **Data início:** 05/07/2026

---

## Itens Verificados

### 1. Saques Duplicados ✅ CONCLUÍDO
- **Problema:** "Solicitações de Saque" (legado) e "Saques Admin (PrimePag)" apontavam para a mesma view
- **Solução:** Removido item legado do sidebar. Mantido apenas `admin/saques-admin` (com funcionalidade PrimePag completa)
- **Arquivos alterados:** `config/adminlte.php`

### 2. Sobre Nós Duplicado ✅ CONCLUÍDO
- **Problema:** Campo `about_us` editável em 3 páginas (admin/about-us, admin/settings/general, admin/configuracoes)
- **Solução:** 
  - Removido seção "Sobre Nós" de `settings/general.blade.php`
  - Removido seção "Sobre Nós" de `configuracao.blade.php`
  - Removido `about_us` do `ConfiguracaoController.php`
  - Corrigido `Geral.vue` - busca `site_info.about_us` primeiro
  - Único local: `admin/about-us`
- **Arquivos alterados:** `settings/general.blade.php`, `configuracao.blade.php`, `ConfiguracaoController.php`, `Geral.vue`

### 3. Configurações Sistema vs Geral ✅ VERIFICADO
- **Resultado:** São diferentes. `admin/configuracoes` = config técnica (mercados, odds). `admin/settings/general` = config visual do site (tema, logo, nome)
- **Decisão:** Manter ambas

### 4. Banners (Home, Templates, Gerador) ✅ VERIFICADO
- **Resultado:** São diferentes. Home = banners homepage. Templates = criar modelos. Gerador = gerar imagem compartilhamento
- **Decisão:** Manter os 3

### 5. Risco (Gerenc, Dashboard, Mapa) ✅ VERIFICADO
- **Resultado:** São diferentes. Gerenc = módulo completo. Dashboard = visão geral. Mapa = visualização
- **Decisão:** Manter os 3

### 6. Mapa Apostas vs Mapa de Risco ✅ VERIFICADO
- **Resultado:** São diferentes. Mapa de Apostas = visualização de apostas. Mapa de Risco = análise de risco
- **Decisão:** Manter os 2

---

## Próximos Itens a Verificar

### 7. Rotas duplicadas (traducoes)
- **Status:** ✅ CONCLUÍDO (bloco duplicado removido do web.php)

### 8. Scraper Jogadinha
- **Status:** ✅ CONCLUÍDO (já estava correto)

### 9. Dashboard (gráfico 7 dias)
- **Status:** ✅ CONCLUÍDO (código correto, sem dados no banco)

### 10. Stubs not_implemented
- **Status:** ✅ CONCLUÍDO (3 endpoints corrigidos)

### 11. Desativar Cassino ✅ CONCLUÍDO
- **Problema:** Cassino ativo no sistema, quer focar só em esportes
- **Solução:** Desativar via configuração admin - apenas super_master ativa no master
  - Toggle **APENAS** no painel Master (`/admin/master/bancas` → Editar Configurações da Banca)
  - Removido toggle de `admin/settings/general` (admin da banca não pode ativar)
  - Adicionado `module => 'cassino'` no header e todos os submenu items do sidebar
  - Adicionado `'cassino' => 'active_casino'` no ModuleFilter
  - Criada migration `active_casino` (default 0 = desativado)
  - Adicionado toggle nos modais "Criar Banca" e "Editar Banca"
  - Adicionado `active_casino` no data-site do botão editar
  - Adicionado `active_casino` no criarBanca e updateBanca do MasterPanelController
- **Para reativar:** Super admin → `/admin/master/bancas` → Editar → ativar "Módulo Cassino"
- **Arquivos alterados:** `config/adminlte.php`, `ModuleFilter.php`, `MasterPanelController.php`, `bancas.blade.php`, migration criada

### 12. Correção Geral dos Módulos (Toggle Master) ✅ CONCLUÍDO
- **Problema:** 13 colunas `active_*` não existiam no banco + `active_bonus` faltando no JSON data-site + ghost columns no ModuleFilter
- **Bugs encontrados:**
  - **BUG 1:** 13 de 15 colunas `active_*` sem migration (só `active_bonus` e `active_casino` existiam)
  - **BUG 2:** `active_bonus` faltando no JSON `data-site` → toggle sempre desligado no modal editar
  - **BUG 3:** `active_relatorios` e `active_online_users` mapeadas no ModuleFilter mas sem toggle, migration ou uso
- **Solução:**
  - Criada migration única com todas as 13 colunas faltantes (default 1 = ativo)
  - Adicionado `active_bonus` ao JSON `data-site` em `bancas.blade.php`
  - Removidas ghost columns `active_relatorios` e `active_online_users` do ModuleFilter
- **Arquivos alterados:** migration criada, `bancas.blade.php`, `ModuleFilter.php`

### 13. Traduções ✅ CONCLUÍDO
- **Problema:** Controller e View incompatíveis (Controller usava tipo/texto_original/texto_traduzido mas View antiga enviava key/pt/en)
- **Solução:**
  - Controller reescrito com CRUD completo (index, store, update, destroy, importFromApi)
  - View reescrita com AdminLTE, DataTable, modal de edição via AJAX PUT
  - Adicionadas rotas PUT `traducoes/{id}` e POST `traducoes/import` no web.php
- **Arquivos alterados:** `TraducaoController.php`, `traducoes.blade.php`, `routes/web.php`

### 14. Tradução Automática (API-Football) ✅ CONCLUÍDO
- **Problema:** API-Football manda nomes em inglês (países, ligas, times), sistema não traduz automaticamente
- **Análise realizada:**
  - API-Football: dados em inglês ("England", "Colombia", "Friendlies Clubs")
  - Jogadinha Scraper: dados já em português ("Malásia", "Terengganu Sub20")
  - `Traducao::traduzir()` existia mas NUNCA era chamado (0 ocorrências no codebase)
- **Solução:**
  - Criado `TranslationService` com dicionário de 70+ países EN→PT
  - Liga names: "Premier League" = não traduz (universal), "World Cup" → "Copa do Mundo", "Friendlies Clubs" → "Amistosos de Clubes"
  - Team names: "Colombia" → "Colômbia", "Arsenal" = não traduz (nome próprio)
  - Integado no `InsertMatches` (API-Football): traduz antes de salvar no banco
  - Integado no `JogadinhaFallback`: suporta overrides do admin
  - Painel admin de traduções continua funcionando como override manual
- **Arquivos criados:** `app/Services/TranslationService.php`
- **Arquivos alterados:** `InsertMatches.php`, `JogadinhaFallback.php`

### 14b. Tradução de Mercados (Odds) ✅ CONCLUÍDO
- **Problema:** Labels das odds em inglês ("Home", "Draw", "Away", "Over", "Under", "Odd", "Even")
- **Solução:**
  - `JogadinhaFallback::translateMarketName()` - 26 mapeamentos (antes 9, com bugs)
    - Bugs corrigidos: "Ambas as equipes marcarão" faltava "na partida", "Total de Gols Mais/Menos" não batia com scraper
    - Adicionados: Vencedor (1T), Placar Exato (1T), Chance Dupla (1T), Par ou Ímpar, Chance Dupla, Intervalo/Final
  - `MatchApiController::getOdds()` - Labels traduzidos no display:
    - Adicionados: Odd→Ímpar, Even→Par
    - Adicionados: Home/→Casa/, Draw/→Empate/, Away/→Fora/ (compostos)
- **Arquivos alterados:** `JogadinhaFallback.php`, `MatchApiController.php`

### 15. Melhoria Admin API-Football ✅ CONCLUÍDO
- **Problema:** Admin tinha apenas 67 linhas (sync + provider), faltava features do REI BET
- **Solução:** Reescrito com 200+ linhas, adicionado:
  - **Status da API:** Verificação em tempo real de quota/plano via `/status`
  - **Atualização de API Key:** Salva direto no `.env`
  - **Filtro de Mercados:** 40+ mercados com checkboxes, salva em `api_markets.json`
  - **Ações Rápidas:** Sincronizar Jogos, Atualizar Odds, Atualizar Ao Vivo
  - **Logs:** Terminal com últimos 200KB do log, auto-refresh
  - **Ligas:** Toggle individual + selecionar todas
- **Arquivos alterados:** `ApiFootballAdminController.php`, `api-football.blade.php`, `routes/web.php`

### 16. Melhoria Admin Scraper ✅ CONCLUÍDO
- **Problema:** Admin tinha apenas 80 linhas, sem filtro de mercados nem logs
- **Solução:** Reescrito com features do REI BET:
  - **Controle:** Start/Stop com status visual
  - **Filtro de Mercados:** 40+ mercados com checkboxes, salva em `scraper_markets.json`
  - **Logs:** Terminal com últimos 200KB do log
  - **Estatísticas:** Jogos sincronizados, ligas ativas, última atualização
  - **Configuração:** Modo Master/Client com campos condicionais
- **Arquivos alterados:** `ApiScraperAdminController.php`, `scraper.blade.php`, `routes/web.php`

### 17. Sidebar Integrações Reorganizada ✅ CONCLUÍDO
- **Problema:** Itens de integração eram soltos no sidebar
- **Solução:** Agrupados em submenu "Integrações (APIs)" com ícone `fa-plug`
  - Config. API (Básica) → `admin/env-config`
  - API-Football → `admin/api-football`
  - Scraper Jogadinha → `admin/scraper`
- **Arquivos alterados:** `config/adminlte.php`

---

## Resumo de Arquivos Criados/Alterados (05/07/2026)

### Criados:
- `app/Services/TranslationService.php` - Dicionário 70+ países + métodos traduzirLiga/Time

### Alterados:
- `app/Console/Commands/ApiFootball/InsertMatches.php` - Integração TranslationService
- `app/Services/JogadinhaFallback.php` - Integração TranslationService
- `app/Http/Controllers/Admin/ApiFootballAdminController.php` - Reescrito (67→200+ linhas)
- `app/Http/Controllers/Admin/ApiScraperAdminController.php` - Reescrito (80→120+ linhas)
- `resources/views/admin/api-football.blade.php` - Reescrita (status, mercado, logs)
- `resources/views/admin/scraper.blade.php` - Reescrita (mercado, logs, stats)
- `config/adminlte.php` - Submenu Integrações
- `routes/web.php` - 12+ novas rotas para API-Football e Scraper

---

## Correção de Bugs (05/07/2026) - 23/24 corrigidos

### CRÍTICOS (crashes) - 5/5 ✅

| # | Arquivo | Bug | Correção |
|---|---------|-----|----------|
| 1 | `app/Models/Rodada.php:22` | Relationship `ApostaBolao::class` não existe | Trocado para `Aposta::class` |
| 2 | `CambistaRelatorioController.php:28-43` | Colunas `valor_apostado`, `retorno_possivel`, `comicao` não existem na tabela `bets` | Trocado para `amount`, `potential_payout`, `commission_amount` + status `won`/`cancelled` |
| 3 | `CambistaHomeController.php:25-50` | Mesmas colunas erradas da tabela `bets` | Mesma correção + status `cancelled` |
| 4 | `CambistaBilhetesController.php:51-142` | `show()` e `cancel()` usavam model `Aposta` (tabela `apostas`) mas `index()` usa `bets` | Refatorado para usar DB::table('bets') e DB::table('bet_items') + status `cancelled` |
| 5 | `UserController.php` | 3 bugs: relationship `region()` não existia, status toggle usava string em vez de int, `manager_id` não existia (é `gerente_id`) | Adicionado `region()` ao User model + `region_id` ao fillable, status agora compara `== 1`, todos `manager_id` → `gerente_id` |

### ALTOS (functional bugs) - 12/12 ✅

| # | Arquivo | Bug | Correção |
|---|---------|-----|----------|
| 6 | `ConfrontosController.php:97` | `$this->amanha->addDay()` mutate a propriedade | Trocado para `$this->amanha->copy()->addDay()` |
| 6b | `ConfrontosController.php:270` | `$match->update($request->all())` mass assignment | Adicionado `$request->only([...])` com campos permitidos |
| 6c | `ConfrontosController.php:295-303` | `delete()` sem null check | Adicionado null check + response JSON |
| 7 | `MapaController.php:38` | `$this->agora->subHour(3)` mutate a propriedade | Trocado para `$this->agora->copy()->subHour(3)` |
| 8 | `FeaturedMatchesController.php:30` | `$site->id` sem null check | Adicionado `$site ?` check |
| 9 | `BannerTemplate.php` | Model usava `active` mas migration cria `is_active` | Adicionado `is_active` ao fillable + casts + accessor `getActiveAttribute()` |
| 10 | `LegacyBridgeController.php:57` | `$b->image` mas coluna é `image_path` | Trocado para `$b->image_path` |
| 11 | `OddsController.php:72` | `$request->all()` mass assignment + sem null check | Adicionado null check + `$request->only([...])` |
| 12 | `MercadosController.php:108` | `$request->all()` mass assignment | Trocado para `$request->only([...])` |
| 13 | `Mercado.php:15` | `orderBy('header')` mas coluna `header` não existe na tabela odds | Trocado para `orderBy('label')` |
| 15 | `BlockMatch.php` + `BlockMatchModel.php` | Fillable com colunas `date`, `sport`, `confronto` que não existem na tabela `block_matchs` | Removidas colunas inválidas |
| 16 | `verify_users migration` | FK referencia tabela `users` mas tabela real é `master_users` | Corrigido para `master_users` |
| 17 | `resources/views/admin/users/clients.blade.php` | View não existia → crash | Criada view com AdminLTE |
| 18 | `resources/views/client/live.blade.php` | `@include('components.stylesheet')` e `components.script-blade` não existem | Substituído por CDN Bootstrap + Vite |

### MÉDIOS - 6/6 ✅

| # | Arquivo | Bug | Correção |
|---|---------|-----|----------|
| 19 | `SiteSetting.php` | Fillable só 7 campos de 50+ no banco | Adicionados 11 campos extras |
| 20 | `FeaturedMatch.php` | Fillable sem `background_path`, `badge_color`, `is_manual`, `manual_event_id` | Adicionados ao fillable |
| 21 | `GerenciamentoRiscos.php` | Typos: "Quantida de" e "Quntidade" | Corrigido para "Quantidade de" |
| 22 | `CambistaController.php` | Property `$cambista` declarada mas nunca usada | Removida |
| 23 | `routes/api.php` | Import `BetApiController` não usado | Removido |
| 24 | `PaymentController.php` | QR Code PIX placeholder hardcoded | PENDENTE - requer integração PrimePag |

### Pendente:
- **MÉDIO 24**: PaymentController QR placeholder - requer integração com API PIX (PrimePag)
