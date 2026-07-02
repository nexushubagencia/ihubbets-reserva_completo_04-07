/**
 * Scraper Puppeteer para https://jogadinha.com/
 *
 * Estratégia:
 *   1. Carrega /data/soccer/today para listar os jogos de futebol
 *   2. Para cada jogo, chama /api/site-list-odds/{matchId} para obter TODAS as odds
 *
 * Endpoints:
 *   GET /data/soccer/today                          -> jogos do dia
 *   GET /api/site-list-odds/{id}                    -> odds de um jogo
 *   GET /api/site-list-odds/{id}/score              -> odds completas de um jogo (33 mercados)
 *   GET /api/site-list-odds-live/{id}               -> odds ao vivo de um jogo
 *   GET /data/soccer/tomorrow                       -> jogos de amanhã
 *   GET /data/soccer/live                           -> jogos ao vivo
 *
 * Nota: NÃO existe endpoint público de resultados de jogos passados (placar).
 *       O site só expõe jogos futuros + odds + jogos ao vivo.
 *       As rotas /data/soccer/yesterday, /data/soccer/results, /api/site-match-result etc
 *       existem (500) mas não retornam dados de placar para o público.
 *
 * Uso:
 *   node scraper-jogadinha.js
 *   node scraper-jogadinha.js --tomorrow     (carrega /data/soccer/tomorrow)
 *   node scraper-jogadinha.js --live         (carrega /data/soccer/live)
 *   node scraper-jogadinha.js --out=meu.json
 */

const { connect } = require("puppeteer-real-browser");
const fs = require('fs');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '..', '.env') });

// Auto-detect CHROME_PATH for Linux VPS (aaPanel / CentOS / Ubuntu)
const possiblePaths = [
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
    '/usr/bin/chromium-browser',
    '/usr/bin/google-chrome-stable',
    '/snap/bin/chromium',
    '/usr/local/bin/chromium',
    '/usr/local/bin/chrome'
];

// Busca recursiva dentro da pasta atual (caso tenha baixado via npx puppeteer)
function findChromeLocal(dir, depth = 0) {
    if (depth > 4) return null;
    try {
        const files = fs.readdirSync(dir);
        for (const f of files) {
            const fullPath = path.join(dir, f);
            const stat = fs.statSync(fullPath);
            if (stat.isDirectory()) {
                const found = findChromeLocal(fullPath, depth + 1);
                if (found) return found;
            } else if (f === 'chrome' || f === 'chromium') {
                // se for um executável (básico)
                if (fullPath.includes('linux')) return fullPath;
            }
        }
    } catch(e) {}
    return null;
}

const localChrome = findChromeLocal(__dirname);
if (localChrome) {
    possiblePaths.unshift(localChrome); // Prioridade máxima pro local
}

for (const p of possiblePaths) {
    if (fs.existsSync(p)) {
        process.env.CHROME_PATH = p;
        break;
    }
}

// ---------- CLI ----------
const args = process.argv.slice(2);
const arg = (name, def = null) => {
  const found = args.find(a => a.startsWith(`--${name}=`));
  return found ? found.split('=').slice(1).join('=') : def;
};
const hasFlag = (name) => args.includes(`--${name}`);

const HEADLESS = !(hasFlag('headless=false') || arg('headless', 'true') === 'false');
const OUT_FILE = arg('out', 'jogos-jogadinha.json');
// Modo (today/tomorrow/live)
const MODO = hasFlag('live') ? 'live' : (hasFlag('tomorrow') ? 'tomorrow' : 'today');

const URL_ALVO = 'https://jogadinha.com/';
const ESPERA_MS = parseInt(arg('wait', '12000'), 10);
const TIMEOUT_MS = parseInt(arg('timeout', '180000'), 10);

// ---------- Helpers ----------
function toFloat(v) {
  if (v == null) return null;
  const s = String(v).replace(',', '.').trim();
  const n = parseFloat(s);
  return isNaN(n) || n <= 0 ? null : n;
}

function processarMercadosDetalhados(oddsArray) {
  // oddsArray é o array retornado por /api/site-list-odds/{id}
  // Cada item é um mercado { match_id, name, headers, odds: [...] }
  const mercados = {};
  for (const mercado of (oddsArray || [])) {
    const nome = mercado.name || 'Outros';
    if (!mercados[nome]) mercados[nome] = {};
    for (const o of (mercado.odds || [])) {
      const sel = o.odd || '?';
      const val = toFloat(o.cotacao);
      if (val == null) continue;
      // Se já existe, mantém o primeiro (evita duplicatas suspensas/normais)
      if (!(sel in mercados[nome])) {
        mercados[nome][sel] = val;
      }
    }
  }
  return mercados;
}

function acharMercado1X2(mercados) {
  const chaves = ['Vencedor do Encontro', 'Resultado Final', 'Match Winner', '1X2', 'Resultado', 'Winner'];
  for (const k of chaves) {
    if (mercados[k]) {
      const m = mercados[k];
      const c1 = m['Casa'] ?? m['1'] ?? m['Home'];
      const cx = m['Empate'] ?? m['X'] ?? m['Draw'];
      const c2 = m['Fora'] ?? m['2'] ?? m['Away'];
      if (c1 != null || cx != null || c2 != null) {
        return { '1': c1, 'X': cx, '2': c2 };
      }
    }
  }
  return null;
}

function normalizarJogo(match, leagueInfo) {
  // Primeiro, monta o objeto básico com o 1X2 do endpoint /data/soccer/today
  const mercadosBasico = {};
  for (const o of (match.odds || [])) {
    const grupo = o.group_opp || 'Outros';
    const sel = o.odd || '?';
    const val = toFloat(o.cotacao);
    if (val == null) continue;
    if (!mercadosBasico[grupo]) mercadosBasico[grupo] = {};
    if (!(sel in mercadosBasico[grupo])) mercadosBasico[grupo][sel] = val;
  }

  return {
    id: match.id,
    eventId: match.event_id,
    mandante: match.home,
    visitante: match.away,
    mandanteImg: match.home_img,
    visitanteImg: match.away_img,
    dataHora: match.date || match.date_original,
    dataOriginal: match.date_original,
    liga: leagueInfo?.league || null,
    pais: leagueInfo?.league_country || null,
    ligaCC: leagueInfo?.league_cc || null,
    totalOddsReportado: match.count_odd || 0,
    _mercadosBasico: mercadosBasico,
  };
}

// ---------- Main ----------
(async () => {
  console.log(`[i] Scraper jogadinha.com  (modo: ${MODO})`);
  console.log(`[i] Headless: ${HEADLESS}`);
  console.log(`[i] Sem limite: extraindo todos os jogos e todas as odds`);

  const pArgs = [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--lang=pt-BR,pt',
  ];

  let proxyObj = null;
  if (process.env.SCRAPER_PROXY) {
      try {
          proxyObj = new URL(process.env.SCRAPER_PROXY);
          pArgs.push(`--proxy-server=${proxyObj.protocol}//${proxyObj.host}`);
          console.log(`[i] Proxy configurado: ${proxyObj.host}`);
      } catch (e) {
          console.log(`[!] Erro ao interpretar SCRAPER_PROXY. Verifique o formato no .env`);
      }
  }

  const { browser, page } = await connect({
    headless: false, // O puppeteer-real-browser recomenda headless: false e lida com o Xvfb sozinho no Linux
    args: pArgs,
    turnstile: true,
    disableXvfb: false,
  });

  try {
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'pt-BR,pt;q=0.9' });

    if (proxyObj && (proxyObj.username || proxyObj.password)) {
        await page.authenticate({
            username: proxyObj.username,
            password: proxyObj.password
        });
        console.log(`[i] Autenticação de Proxy Ativada.`);
    }

    await page.setRequestInterception(true);
    page.on('request', (req) => {
      const t = req.resourceType();
      if (t === 'image' || t === 'font' || t === 'media') req.abort();
      else req.continue();
    });

    // === PASSO 1: Pegar lista de jogos ===
    console.log(`[i] Navegando em ${URL_ALVO}... aguardando o carregamento inicial.`);
    await page.goto(URL_ALVO, { waitUntil: 'networkidle2', timeout: TIMEOUT_MS });

    // Aguarda mais um pouco pro Cloudflare (se houver) passar
    await new Promise(r => setTimeout(r, ESPERA_MS));

    // Tirar print para ver se tem Cloudflare bloqueando
    try {
        await page.screenshot({ path: path.join(__dirname, '..', 'public', 'debug-scraper.png'), fullPage: true });
        console.log(`[i] Print salvo em public/debug-scraper.png para diagnóstico`);
    } catch(e) {}

    const endpoint = `/data/soccer/${MODO}`;
    console.log(`[i] Fazendo fetch interno para ${endpoint} ...`);

    let listaJogos = await page.evaluate(async (ep) => {
        try {
            const r = await fetch(ep);
            return await r.json();
        } catch (e) {
            return null;
        }
    }, endpoint);

    if (!Array.isArray(listaJogos) || listaJogos.length === 0) {
      console.log('[!] Não consegui obter a lista de jogos.');
      // Tenta pelo menos mais tempo
      await new Promise(r => setTimeout(r, 5000));
      await browser.close();
      process.exit(1);
    }

    // Achata a lista (jogos estão agrupados por liga)
    const jogosBrutos = [];
    for (const ligaInfo of listaJogos) {
      if (!Array.isArray(ligaInfo.match)) continue;
      for (const match of ligaInfo.match) {
        jogosBrutos.push(normalizarJogo(match, ligaInfo));
      }
    }

    console.log(`[OK] ${jogosBrutos.length} jogos encontrados em ${listaJogos.length} ligas`);

    // === PASSO 2: Buscar odds detalhadas de cada jogo ===
    console.log(`[i] Buscando odds detalhadas de ${jogosBrutos.length} jogos...`);

    const oddsPorJogo = await page.evaluate(async (jogos, liveMode) => {
      const out = {};
      let i = 0;
      for (const jogo of jogos) {
        i++;
        // /api/site-list-odds/{id}/score retorna 33 mercados completos (sempre).
        // /api/site-list-odds-live/{id} é o equivalente para jogos ao vivo.
        const ep = (liveMode ? '/api/site-list-odds-live/' : '/api/site-list-odds/') + jogo.id + (liveMode ? '' : '/score');
        try {
          const r = await fetch(ep, {
            credentials: 'include',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          });
          if (r.ok) {
            out[jogo.id] = await r.json();
          } else {
            // fallback: sem /score
            try {
              const ep2 = (liveMode ? '/api/site-list-odds-live/' : '/api/site-list-odds/') + jogo.id;
              const r2 = await fetch(ep2, { credentials: 'include', headers: { 'Accept': 'application/json' } });
              out[jogo.id] = r2.ok ? await r2.json() : { error: 'status ' + r.status };
            } catch (e2) {
              out[jogo.id] = { error: e.message };
            }
          }
        } catch (e) {
          out[jogo.id] = { error: e.message };
        }
        // Log progresso
        if (i % 10 === 0 || i === jogos.length) {
          console.log(`   ${i}/${jogos.length} processados`);
        }
        // Pequena pausa para não sobrecarregar
        await new Promise(r => setTimeout(r, 50));
      }
      return out;
    }, jogosBrutos, MODO === 'live');

    // === PASSO 3: Juntar tudo ===
    const jogos = jogosBrutos.map((jogo) => {
      const oddsDetalhadas = oddsPorJogo[jogo.id];

      let mercados = jogo._mercadosBasico || {};
      let totalOddsReal = Object.values(mercados).reduce((acc, m) => acc + Object.keys(m).length, 0);

      if (Array.isArray(oddsDetalhadas) && oddsDetalhadas.length > 0) {
        const mercadosDetalhados = processarMercadosDetalhados(oddsDetalhadas);
        // Mescla: detalhado tem prioridade
        mercados = { ...mercados, ...mercadosDetalhados };
      }

      // Aplica o filtro de mercados (Admin Config)
      const configPath = path.join(__dirname, '..', 'storage', 'app', 'scraper_markets.json');
      let ativos = null;
      try {
          if (fs.existsSync(configPath)) {
              ativos = JSON.parse(fs.readFileSync(configPath, 'utf8'));
          }
      } catch (e) {}

      if (ativos !== null && Array.isArray(ativos)) {
          for (const mName of Object.keys(mercados)) {
              if (!ativos.includes(mName)) {
                  delete mercados[mName];
              }
          }
      }

      totalOddsReal = Object.values(mercados).reduce((acc, m) => acc + Object.keys(m).length, 0);

      const { _mercadosBasico, ...resto } = jogo;
      return {
        ...resto,
        mercadoPrincipal: acharMercado1X2(mercados),
        mercados,
        totalMercados: Object.keys(mercados).length,
        totalOdds: totalOddsReal,
      };
    });

    // Ordena por horário
    jogos.sort((a, b) => String(a.dataHora || '').localeCompare(String(b.dataHora || '')));

    const payload = {
      url: URL_ALVO,
      capturadoEm: new Date().toISOString(),
      modo: MODO,
      totalLigas: listaJogos.length,
      totalJogosBrutos: jogosBrutos.length,
      totalJogosExtraidos: jogos.length,
      jogos,
    };

    const outPath = path.isAbsolute(OUT_FILE) ? OUT_FILE : path.resolve(__dirname, OUT_FILE);
    fs.writeFileSync(outPath, JSON.stringify(payload, null, 2), 'utf-8');

    console.log(`\n========================================`);
    console.log(`[OK] ${jogos.length} jogos com TODAS as odds`);
    console.log(`     Salvos em: ${outPath}`);
    console.log(`========================================`);

    if (jogos.length > 0) {
      console.log(`\n[i] Primeiros 5 jogos:`);
      jogos.slice(0, 5).forEach((j, i) => {
        const mp = j.mercadoPrincipal || {};
        const odds = (mp['1'] || mp['X'] || mp['2']) ? `  [1:${mp['1']} X:${mp['X']} 2:${mp['2']}]` : '';
        console.log(`   ${i + 1}. ${j.mandante} x ${j.visitante}  ${j.dataHora || '-'}  ${j.liga || '-'}${odds}`);
        console.log(`        ${j.totalMercados} mercados, ${j.totalOdds} odds`);
      });

      // Estatísticas
      const contagemMercados = {};
      let totalOddsGeral = 0;
      for (const j of jogos) {
        totalOddsGeral += j.totalOdds;
        for (const m of Object.keys(j.mercados)) {
          contagemMercados[m] = (contagemMercados[m] || 0) + 1;
        }
      }
      console.log(`\n[i] Total geral: ${totalOddsGeral} odds em ${Object.keys(contagemMercados).length} mercados diferentes`);
      console.log(`\n[i] Mercados disponíveis (top 20):`);
      Object.entries(contagemMercados)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 20)
        .forEach(([m, c]) => console.log(`   ${c}x  ${m}`));
    }
  } catch (err) {
    console.error('[ERRO]', err);
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
})();
