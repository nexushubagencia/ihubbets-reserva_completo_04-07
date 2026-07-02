#!/bin/bash
cd /www/wwwroot/bet.reidoscript.com.br/scraper-jogadinha
LOG="scraper.log"

echo -e "\n[$(date +'%Y-%m-%d %H:%M:%S')] Iniciando Extração em Background..." >> $LOG

echo -e "\n[$(date +'%Y-%m-%d %H:%M:%S')] HOJE EXTRAÍDO:" >> $LOG
node scraper-jogadinha.js >> $LOG 2>&1
php ../artisan command:fallbackJogadinha
echo -e "\n[$(date +'%Y-%m-%d %H:%M:%S')] HOJE INSERIDO NO BANCO" >> $LOG

echo -e "\n[$(date +'%Y-%m-%d %H:%M:%S')] AMANHÃ EXTRAÍDO:" >> $LOG
node scraper-jogadinha.js --tomorrow --out=jogos-jogadinha-tomorrow.json >> $LOG 2>&1
php ../artisan command:fallbackJogadinhaTomorrow
echo -e "\n[$(date +'%Y-%m-%d %H:%M:%S')] AMANHÃ INSERIDO NO BANCO\n" >> $LOG
