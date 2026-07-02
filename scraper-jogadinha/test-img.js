const { connect } = require('puppeteer-real-browser');
(async () => {
    const { browser, page } = await connect({headless: false});
    await page.goto('https://jogadinha.com/esportes/futebol/amanha');
    await new Promise(r => setTimeout(r, 8000));
    const html = await page.evaluate(() => document.documentElement.outerHTML);
    const fs = require('fs');
    fs.writeFileSync('dom-jogadinha.html', html);
    await browser.close();
})();
