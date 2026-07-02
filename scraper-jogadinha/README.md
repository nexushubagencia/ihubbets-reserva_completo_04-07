# Scraper jogadinha.com

Scraper Puppeteer que extrai os jogos de futebol e **todas as odds** (Vencedor do Encontro, Total de gols, Ambas Marcam, Placar Exato, Chance Dupla, etc.) de [https://jogadinha.com/](https://jogadinha.com/).

## Instalação

```bash
cd scraper-jogadinha
npm install
```

> O Puppeteer baixa o Chromium automaticamente (~170 MB). Se já tiver Chrome instalado, pode definir a variável `PUPPETEER_SKIP_DOWNLOAD=true` e apontar via `executablePath` no script.

## Uso

```bash
# Padrão: jogos de hoje
npm start

# Jogos de amanhã
npm run tomorrow

# Jogos ao vivo
npm run live

# Opções via CLI
node scraper-jogadinha.js --out=meu-arquivo.json
node scraper-jogadinha.js --headless=false
node scraper-jogadinha.js --url=https://jogadinha.com/ --wait=15000
```

### Flags disponíveis

| Flag | Default | Descrição |
|---|---|---|
| `--today` | (padrão) | Carrega `/data/soccer/today` |
| `--tomorrow` | - | Carrega `/data/soccer/tomorrow` |
| `--live` | - | Carrega `/data/soccer/live` |
| `--out=ARQUIVO` | `jogos-jogadinha.json` | Caminho do arquivo de saída |
| `--headless=false` | `true` | Mostra o navegador |
| `--wait=MS` | `12000` | Espera extra após carregar (ms) |
| `--timeout=MS` | `180000` | Timeout total (ms) |
| `--url=URL` | `https://jogadinha.com/` | URL alvo |

> O scraper **não tem limite** de jogos nem de odds: extrai todos os jogos disponíveis e todas as odds de cada mercado.

## Como funciona

A API interna do site é chamada dentro do contexto do navegador (assim o Puppeteer usa os cookies/CSRF corretos):

1. **Lista de jogos**: `GET /data/soccer/today` retorna ligas com seus jogos, agrupados por liga.
2. **Odds detalhadas**: para cada jogo, `GET /api/site-list-odds/{matchId}` retorna **todas** as odds em todos os mercados disponíveis.

## Saída

Arquivo JSON com a estrutura:

```json
{
  "url": "https://jogadinha.com/",
  "capturadoEm": "2026-06-15T04:04:19.402Z",
  "modo": "today",
  "totalLigas": 37,
  "totalJogosBrutos": 57,
  "totalJogosExtraidos": 57,
  "jogos": [
    {
      "id": 1553504,
      "eventId": 196281777,
      "mandante": "Chulalongkorn - Universidade",
      "visitante": "Kasem Bundit University",
      "dataHora": "2026-06-15 05:30:00",
      "liga": "Tailândia - Jogos Universitários",
      "pais": "Tailândia",
      "mercadoPrincipal": { "1": 1.48, "X": 4.2, "2": 5.25 },
      "mercados": {
        "Vencedor do Encontro":         { "Casa": 1.48, "Empate": 4.2, "Fora": 5.25 },
        "Ambas as equipes marcarão":   { "Sim": 1.49, "Não": 1.49 },
        "Placar Exato Tempo Completo": { "0X0": 15, "1X0": 9.5, "2X1": 9.5, ... },
        ...
      },
      "totalMercados": 11,
      "totalOdds": 73
    }
  ]
}
```

## Mercados disponíveis

Vencedor do Encontro · Total de gols na partida · Ambas as equipes marcarão · Empate Anula Aposta · Chance Dupla · Placar Exato (TC e 1T) · Vencedor ao Intervalo | Vencedor Final · Par ou Ímpar · Total de Escanteios · Vencedor (1T) · Time - Total de gols · Casa/Tempo com mais gols · e mais…

## Requisitos

- Node.js >= 18
- Windows / macOS / Linux
- ~250 MB de espaço em disco (Chromium do Puppeteer)
