const { connect } = require("puppeteer-real-browser");

(async () => {
    console.log("[i] Verificando endpoints de resultados da Jogadinha...");
    
    const endpoints = [
        "https://jogadinha.com/data/soccer/yesterday",
        "https://jogadinha.com/data/soccer/results",
        "https://jogadinha.com/api/site-match-result",
        "https://jogadinha.com/results"
    ];

    const { browser, page } = await connect({
        headless: false,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
        turnstile: true,
        disableXvfb: false,
    });

    try {
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
        
        console.log("[i] Acessando a página inicial para driblar o Cloudflare...");
        await page.goto("https://jogadinha.com/", { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 5000));

        for (const ep of endpoints) {
            console.log(`[i] Testando endpoint: ${ep}`);
            try {
                const response = await page.goto(ep, { waitUntil: 'networkidle2', timeout: 15000 });
                const status = response.status();
                const text = await page.evaluate(() => document.body.innerText.substring(0, 200));
                console.log(` -> Status: ${status}`);
                console.log(` -> Retorno: ${text.replace(/\n/g, ' ')}\n`);
            } catch (e) {
                console.log(` -> Erro: ${e.message}\n`);
            }
        }
    } catch (e) {
        console.error("Erro geral:", e);
    } finally {
        await browser.close();
    }
})();
