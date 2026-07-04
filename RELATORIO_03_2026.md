# RELATÓRIO DIÁRIO — 03/07/2026

## Resumo do Dia

Trabalho focado em: **correção de bugs**, **auditoria completa do sistema**, **otimização de performance** e **organização do Git**.

---

## ✅ O Que Foi Feito

### 1. Fix: Deslocamento "IHUB BETS" ao Abrir Modal (Desktop + Mobile)
**Problema:** O nome "IHUB BRETS" no header se movia para a direita quando um modal abria.

**Causas encontradas (3):**
- Bootstrap adicionava `padding-right` no body ao abrir modal
- `_forceLogoVisible()` no `front_nexus_hibrido.js` forçava `display:flex` e `width:230px` no logo
- CSS inline no `Geral.vue` injetava `width:230px !important` no logo via `<style>` tag

**Correções:**
- `welcome.blade.php`: MutationObserver + interceptação de `CSSStyleDeclaration.prototype.setProperty`
- `custom.css`: `body.modal-open` zera `padding-right` em body/wrapper/header
- `Geral.vue`: Removido `width:230px !important` do CSS inline do modal (recompilado com npm)

### 2. Polling Profissional (Sem Piscar)
**Problema:** Site piscava a cada 30s na home e 15s no ao vivo.

**Correção (source + bundle + backup):**
- `Geral.vue`: `_matchRefreshInterval` 30s→300s (5min), `_liveRefreshInterval` 15s→30s
- Recompilado com `npm run production`
- Monkey-patch no `welcome.blade.php` como backup

### 3. Atualização de Dados Esportivos
- `apifootball:insert_matches`: Alterado para buscar **5 dias** à frente
- **321 partidas** de futebol inseridas (69 hoje + 252 amanhã)
- Odds atualizadas para 25 partidas (quota free)
- Cache reconstruído: home, liveHoje, liveAmanha

### 4. Tabela `block_matches` Criada
Tabela faltando no banco causava erro nos comandos de cache.

### 5. Endpoints API Novos
- `GET /api/live-scores` — placares ao vivo (leve)
- `GET /api/home-matches` — partidas da home (cache 5min)
- `LiveScoresController.php` + `UpdateLiveScores.php`

### 6. Auditoria Completa do Sistema (14 itens corrigidos)

**CRITICAL (5):**
| Fix | Arquivo |
|-----|---------|
| `auth('api')` → `auth()` | DepositosController, PlayfiverController, SaquesApiController, BonusController |
| `Game::` → `MatchModel::` | SettleApiBets.php |
| `MatchEvent::` → `MatchModel::` | BilheteApiController.php |
| Desabilita schedules BetsAPI (403) | Kernel.php |
| RateLimiterService no LiveOdds | LiveOdds.php |

**IMPORTANT (4):**
| Fix | Arquivo |
|-----|---------|
| Remove BROADCAST_CONNECTION duplicado | .env |
| `dispatchNow` → `dispatchSync` | LiveScoreMultiSport.php |
| Versão 1.0.0 → 2.1.0 | adminlte.php |
| Desabilita off-season schedules | Kernel.php |

### 7. Backup dos Fontes `.vue`
- **37 arquivos** `.vue` salvos em `storage/backup/vue-source-2026-07-03/`
- Commitados no Git

### 8. Organização Git
- Branch `dev` criada e pushed
- Fluxo de trabalho: `dev` (edição) → `main` (estável)
- 10 commits hoje, todos pushed

---

## 📋 O Que Fazer Amanhã (04/07/2026)

### Prioridade ALTA
1. **Testar depósitos PIX** — O fix `auth()` foi aplicado mas não testado com transação real
2. **Testar Casino Playfiver** — Mesmo fix, precisa validar auth
3. **Rodar `apifootball:update_odds`** — Cobrir mais partidas com odds (só 25/dia no free)
4. **Verificar live scores** — Testar se `apifootball:live` funciona com RateLimiter

### Prioridade MÉDIA
5. **Agendar cache refresh** — Adicionar `command:atualizaHome` no Kernel schedule
6. **Scraper Jogadinha** — Criar `jogos-jogadinha-live.json` (falta)
7. **Testar cash-out** — Validar fluxo completo
8. **Verificar saques** — Fluxo admin com PrimePag

### Prioridade BAIXA
9. **Limpar rotas stub** — Muitas rotas retornam "not_implemented"
10. **Atualizar AGENTS.md** — Refletir comandos corretos e status atual

### Observações
- Basquete e vôlei estão **off-season** (voltam set/out)
- API-Football free: **100 req/dia** — usar com cuidado
- Token BetsAPI **inválido** (403) — desabilitado no schedule
- Tênis e MMA **não disponíveis** na API-Football

---

## 📊 Números do Dia

| Métrica | Valor |
|---------|-------|
| Commits | 10 |
| Arquivos alterados | 20+ |
| Bugs critical corrigidos | 5 |
| Bugs important corrigidos | 4 |
| Partidas no banco | 321 |
| Odds no banco | 13.318 |
| Arquivos .vue backup | 37 |
| Branches criadas | 1 (dev) |

---

## 🔗 Links Úteis

- **Repositório:** https://github.com/nexushubagencia/ihub-bets-v2
- **Branch main:** código estável
- **Branch dev:** código de desenvolvimento
- **Servidor local:** http://127.0.0.1:8000
- **Laravel:** 13.5.0
- **API Key:** `a97729bf9b4aa0e0a2d1d6a270ab003e` (Free, 100 req/dia)
