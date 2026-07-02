const cron = require('node-cron');
const { exec } = require('child_process');
const ftp = require("basic-ftp");
const path = require('path');
const fs = require('fs');

// ============================================
// CONFIGURAÇÕES DE FTP (PREENCHA AQUI!)
// ============================================
const FTP_HOST = "SEU_IP_AQUI"; // Exemplo: 154.23.12.34
const FTP_USER = "SEU_USUARIO_AQUI"; 
const FTP_PASS = "SUA_SENHA_AQUI";

// O caminho exato onde o arquivo deve ser salvo lá na VPS
const FTP_REMOTE_PATH = "/www/wwwroot/bet.reidoscript.com.br/scraper-jogadinha/jogos-jogadinha.json";
// ============================================

console.log("[AUTO-SYNC] Serviço iniciado! O Scraper rodará a cada 5 minutos.");

async function uploadFTP() {
    const client = new ftp.Client();
    try {
        console.log("[FTP] Conectando à VPS...");
        await client.access({
            host: FTP_HOST,
            user: FTP_USER,
            password: FTP_PASS,
            secure: false
        });
        console.log("[FTP] Conectado. Enviando arquivos para o servidor...");
        if (fs.existsSync("jogos-jogadinha.json")) {
            await client.uploadFrom("jogos-jogadinha.json", FTP_REMOTE_PATH);
        }
        if (fs.existsSync("jogos-jogadinha-tomorrow.json")) {
            // Assume que o caminho do tomorrow tem o mesmo prefixo, mas termina com jogos-jogadinha-tomorrow.json
            const remoteTomorrow = FTP_REMOTE_PATH.replace('jogos-jogadinha.json', 'jogos-jogadinha-tomorrow.json');
            await client.uploadFrom("jogos-jogadinha-tomorrow.json", remoteTomorrow);
        }
        console.log("[FTP] Arquivos atualizados na VPS com sucesso!");
    }
    catch(err) {
        console.error("[FTP] Erro na transferência FTP:", err.message);
    }
    client.close();
}

function runScraper() {
    console.log(`\n[${new Date().toLocaleString()}] Iniciando processo automático...`);

    // Checa a configuração do painel Admin (.env)
    const envPath = path.join(__dirname, '..', '.env');
    if (fs.existsSync(envPath)) {
        const envContent = fs.readFileSync(envPath, 'utf8');
        if (envContent.includes('SCRAPER_JOGADINHA_ENABLED=false')) {
            console.log('[Scraper] O Scraper está DESATIVADO no painel. O robô não vai rodar.');
            return;
        }
    }

    exec('node scraper-jogadinha.js', (error, stdout, stderr) => {
        if (error) {
            console.error(`[Scraper HOJE] Erro: ${error.message}`);
        } else {
            console.log(`[Scraper HOJE] Extração concluída!`);
        }
        
        // Em seguida, extrai os jogos de amanhã
        exec('node scraper-jogadinha.js --tomorrow --out=jogos-jogadinha-tomorrow.json', async (error2, stdout2, stderr2) => {
            if (error2) {
                console.error(`[Scraper AMANHÃ] Erro: ${error2.message}`);
            } else {
                console.log(`[Scraper AMANHÃ] Extração concluída!`);
            }

            if (fs.existsSync('jogos-jogadinha.json') || fs.existsSync('jogos-jogadinha-tomorrow.json')) {
                await uploadFTP();
            } else {
                console.log("[Scraper] Nenhum arquivo JSON gerado nesta rodada.");
            }
        });
    });
}

// Roda automaticamente de 5 em 5 minutos
cron.schedule('*/5 * * * *', () => {
    runScraper();
});

// Inicia uma primeira vez assim que abrir o programa
runScraper();
