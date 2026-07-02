const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
puppeteer.use(StealthPlugin());

(async () => {
    const browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox'] });
    const page = await browser.newPage();
    const urls = new Set();
    
    page.on('response', response => {
        const url = response.url();
        if (url.includes('cloudfront.net') || url.includes('flag')) {
            urls.add(url);
        }
    });

    await page.goto('https://jogadinha.com/esportes/futebol/hoje', { waitUntil: 'networkidle2' });
    
    console.log(Array.from(urls).join('\n'));
    await browser.close();
})();
