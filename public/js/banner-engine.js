/**
 * BannerEngine — Canvas-based banner generator
 * PayBets / MyBetServer White Label
 * 
 * Usage:
 *   const engine = new BannerEngine();
 *   const canvas = await engine.generateSingleGame(data, template);
 *   engine.download(canvas, 'banner.png');
 */

class BannerEngine {
    constructor() {
        this._cache   = {};
        this._accent  = '#22c55e';
        this._fallback = this._mkPlaceholder();
    }

    /* ── helpers ─────────────────────────────────────────── */

    _mkPlaceholder() {
        const s = `<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120">
            <circle cx="60" cy="60" r="55" fill="#1a1a2e" stroke="#333" stroke-width="2"/>
            <text x="60" y="68" font-size="36" fill="#444" text-anchor="middle" font-family="Arial">?</text>
        </svg>`;
        return 'data:image/svg+xml;base64,' + btoa(s);
    }

    async loadImage(url) {
        if (!url) return this._loadFallback();
        if (this._cache[url]) return this._cache[url];
        return new Promise(resolve => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            const timer = setTimeout(() => resolve(this._loadFallback()), 6000);
            img.onload  = () => { clearTimeout(timer); this._cache[url] = img; resolve(img); };
            img.onerror = () => { clearTimeout(timer); resolve(this._loadFallback()); };
            img.src = url;
        });
    }

    _loadFallback() {
        return new Promise(r => {
            const img = new Image();
            img.onload = () => r(img);
            img.src = this._fallback;
        });
    }

    rr(ctx, x, y, w, h, r = 8) {   /* roundRect */
        if (w < 2*r) r = w/2;
        if (h < 2*r) r = h/2;
        ctx.beginPath();
        ctx.moveTo(x+r, y);
        ctx.arcTo(x+w, y,   x+w, y+h, r);
        ctx.arcTo(x+w, y+h, x,   y+h, r);
        ctx.arcTo(x,   y+h, x,   y,   r);
        ctx.arcTo(x,   y,   x+w, y,   r);
        ctx.closePath();
    }

    clipCircle(ctx, cx, cy, r) {
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI*2); ctx.clip();
    }

    drawImgContain(ctx, img, x, y, w, h) {
        if (!img) return;
        const s = Math.min(w/img.width, h/img.height);
        const sw = img.width*s, sh = img.height*s;
        ctx.drawImage(img, x+(w-sw)/2, y+(h-sh)/2, sw, sh);
    }

    drawImgCircle(ctx, img, cx, cy, r) {
        if (!img) return;
        ctx.save(); this.clipCircle(ctx, cx, cy, r);
        ctx.drawImage(img, cx-r, cy-r, r*2, r*2);
        ctx.restore();
    }

    ellipsis(ctx, text, x, y, maxW, textAlign = 'center') {
        ctx.textAlign = textAlign;
        if (ctx.measureText(text).width <= maxW) { ctx.fillText(text, x, y); return; }
        while (text.length && ctx.measureText(text + '…').width > maxW) text = text.slice(0,-1);
        ctx.fillText(text + '…', x, y);
    }

    glow(ctx, color, blur = 20) { ctx.shadowColor = color; ctx.shadowBlur = blur; }
    noGlow(ctx) { ctx.shadowBlur = 0; ctx.shadowColor = 'transparent'; }

    /* ════════════════════════════════════════════════════════
       SINGLE GAME BANNER — botão laranja da home (1080×1350)
       ════════════════════════════════════════════════════════ */
    async generateSingleGame(data, tpl = {}) {
        const W = 1080, H = 1350;
        const c = document.createElement('canvas');
        c.width = W; c.height = H;
        const ctx = c.getContext('2d');
        const A = tpl.accentColor || this._accent;

        /* — background — */
        if (tpl.backgroundUrl) {
            const bg = await this.loadImage(tpl.backgroundUrl);
            ctx.drawImage(bg, 0, 0, W, H);
            ctx.fillStyle = `rgba(0,0,0,${tpl.overlayOpacity ?? 0.55})`;
            ctx.fillRect(0, 0, W, H);
        } else {
            const g = ctx.createLinearGradient(0, 0, W, H);
            g.addColorStop(0,   '#04080a');
            g.addColorStop(0.5, '#0b1f0e');
            g.addColorStop(1,   '#04080a');
            ctx.fillStyle = g; ctx.fillRect(0, 0, W, H);
            /* grid decoration */
            ctx.strokeStyle = 'rgba(34,197,94,0.04)'; ctx.lineWidth = 1;
            for (let i=0;i<W;i+=80){ctx.beginPath();ctx.moveTo(i,0);ctx.lineTo(i,H);ctx.stroke();}
            for (let i=0;i<H;i+=80){ctx.beginPath();ctx.moveTo(0,i);ctx.lineTo(W,i);ctx.stroke();}
        }

        /* — neon side bars — */
        this.glow(ctx, A, 25);
        ctx.fillStyle = A;
        ctx.fillRect(0, 0, 8, H);
        ctx.fillRect(W-8, 0, 8, H);
        this.noGlow(ctx);

        /* — HEADER band — */
        ctx.fillStyle = 'rgba(0,0,0,0.88)';
        ctx.fillRect(0, 0, W, 210);
        ctx.fillStyle = A; ctx.fillRect(0, 210, W, 5);

        /* site logo */
        if (data.siteLogo) {
            const logo = await this.loadImage(data.siteLogo);
            ctx.save(); this.rr(ctx, 28, 15, 170, 170, 12); ctx.clip();
            this.drawImgContain(ctx, logo, 28, 15, 170, 170);
            ctx.restore();
        }

        /* title */
        const rawTitle = data.title || 'FAÇA SUA APOSTA AGORA!';
        const [l1, ...rest] = rawTitle.split('!').map(s=>s.trim()).filter(Boolean);
        ctx.textAlign = 'left';
        ctx.font = `bold 52px 'Arial Black', Arial, sans-serif`;
        ctx.fillStyle = '#ffffff';
        ctx.fillText(l1 + '!', 220, 105);
        if (rest.length) {
            ctx.font = `bold 40px 'Arial Black', Arial, sans-serif`;
            ctx.fillStyle = A;
            ctx.fillText(rest.join('!') + '!', 220, 168);
        }

        /* — LEAGUE badge — */
        ctx.fillStyle = 'rgba(0,0,0,0.75)';
        this.rr(ctx, W/2-260, 236, 520, 58, 29); ctx.fill();
        ctx.strokeStyle = A; ctx.lineWidth = 2;
        this.rr(ctx, W/2-260, 236, 520, 58, 29); ctx.stroke();
        ctx.font = `bold 26px Arial, sans-serif`;
        ctx.fillStyle = '#ffffff'; ctx.textAlign = 'center';
        ctx.fillText(data.league || '', W/2, 273);

        /* — TEAMS — */
        const tY = 480, aX = W*0.25, bX = W*0.75, lr = 130;

        [aX, bX].forEach(cx => {
            ctx.beginPath(); ctx.arc(cx, tY, lr+12, 0, Math.PI*2);
            ctx.fillStyle = 'rgba(255,255,255,0.06)'; ctx.fill();
            ctx.strokeStyle = `${A}55`; ctx.lineWidth = 2; ctx.stroke();
        });

        const [imgA, imgB] = await Promise.all([
            this.loadImage(data.teamALogo),
            this.loadImage(data.teamBLogo)
        ]);
        this.drawImgCircle(ctx, imgA, aX, tY, lr);
        this.drawImgCircle(ctx, imgB, bX, tY, lr);

        /* VS */
        ctx.font = `bold 110px 'Arial Black', Arial, sans-serif`;
        ctx.textAlign = 'center'; ctx.fillStyle = A;
        this.glow(ctx, A, 35); ctx.fillText('VS', W/2, tY+35); this.noGlow(ctx);

        /* team names */
        ctx.font = `bold 36px Arial, sans-serif`; ctx.fillStyle = '#ffffff';
        this.ellipsis(ctx, data.teamA||'', aX, tY+lr+52, 330);
        this.ellipsis(ctx, data.teamB||'', bX, tY+lr+52, 330);

        /* — DATE/TIME pill — */
        const pY = tY + lr + 100;
        ctx.fillStyle = 'rgba(0,0,0,0.82)';
        this.rr(ctx, W/2-195, pY, 390, 60, 30); ctx.fill();
        ctx.strokeStyle = A; ctx.lineWidth = 1.5;
        this.rr(ctx, W/2-195, pY, 390, 60, 30); ctx.stroke();
        ctx.font = `bold 26px Arial, sans-serif`;
        ctx.fillStyle = '#cccccc'; ctx.textAlign = 'center';
        ctx.fillText('📅 ' + (data.dateTime||''), W/2, pY+38);

        /* — ODDS band — */
        const oY = 870;
        ctx.fillStyle = 'rgba(0,0,0,0.92)'; ctx.fillRect(0, oY, W, 185);
        ctx.fillStyle = A; ctx.fillRect(0, oY, W, 5); ctx.fillRect(0, oY+180, W, 5);

        const odds = [
            {lbl:'CASA',   val: data.oddHome||'-', x: W*0.2},
            {lbl:'EMPATE', val: data.oddDraw||'-', x: W*0.5},
            {lbl:'FORA',   val: data.oddAway||'-', x: W*0.8},
        ];
        odds.forEach(o => {
            ctx.font = `14px Arial, sans-serif`; ctx.fillStyle = 'rgba(255,255,255,0.45)';
            ctx.textAlign = 'center'; ctx.fillText(o.lbl, o.x, oY+34);
            ctx.font = `bold 75px 'Arial Black', Arial, sans-serif`; ctx.fillStyle = A;
            this.glow(ctx, A, 18); ctx.fillText(o.val, o.x, oY+160); this.noGlow(ctx);
        });
        ctx.strokeStyle = 'rgba(255,255,255,0.12)'; ctx.lineWidth = 1;
        [W/3, W*2/3].forEach(x => {
            ctx.beginPath(); ctx.moveTo(x, oY+18); ctx.lineTo(x, oY+168); ctx.stroke();
        });

        /* — FOOTER — */
        const fY = H - 110;
        ctx.fillStyle = 'rgba(0,0,0,0.92)'; ctx.fillRect(0, fY, W, 110);
        ctx.fillStyle = A; ctx.fillRect(0, fY, W, 4);

        ctx.font = `bold 28px Arial, sans-serif`; ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center'; ctx.fillText(data.siteUrl||'', W/2, fY+52);

        if (data.instagram) {
            ctx.font = `21px Arial, sans-serif`; ctx.fillStyle = '#999999';
            ctx.fillText(data.instagram, W/2, fY+88);
        }

        /* sideways disclaimer */
        ['left','right'].forEach(side => {
            ctx.save();
            ctx.translate(side==='left' ? 16 : W-16, H/2);
            ctx.rotate(side==='left' ? -Math.PI/2 : Math.PI/2);
            ctx.font = `12px Arial`; ctx.fillStyle = 'rgba(255,255,255,0.25)';
            ctx.textAlign = 'center'; ctx.fillText('cotações podem mudar', 0, 0);
            ctx.restore();
        });

        return c;
    }

    /* ════════════════════════════════════════════════════════
       MULTI GAME BANNER — Gerador de banners admin (1080×1080)
       ════════════════════════════════════════════════════════ */
    async generateMultiGame(data, tpl = {}) {
        const W = 1080, H = 1080;
        const c = document.createElement('canvas');
        c.width = W; c.height = H;
        const ctx = c.getContext('2d');
        const A = tpl.accentColor || this._accent;
        const games = data.games || [];

        /* — background — */
        if (tpl.backgroundUrl) {
            const bg = await this.loadImage(tpl.backgroundUrl);
            ctx.drawImage(bg, 0, 0, W, H);
            ctx.fillStyle = `rgba(0,0,0,${tpl.overlayOpacity ?? 0.58})`;
            ctx.fillRect(0, 0, W, H);
        } else {
            const g = ctx.createRadialGradient(W/2, H/2, 0, W/2, H/2, H*0.8);
            g.addColorStop(0, '#142918'); g.addColorStop(1, '#050e05');
            ctx.fillStyle = g; ctx.fillRect(0, 0, W, H);
            /* field lines */
            ctx.strokeStyle = 'rgba(255,255,255,0.04)'; ctx.lineWidth = 2;
            ctx.beginPath(); ctx.arc(W/2, H*0.55, 200, 0, Math.PI*2); ctx.stroke();
            ctx.strokeRect(80, 260, W-160, H-340);
            ctx.beginPath(); ctx.moveTo(W/2, 260); ctx.lineTo(W/2, H-80); ctx.stroke();
        }

        /* — HEADER — */
        const hH = 162;
        ctx.fillStyle = 'rgba(0,0,0,0.88)'; ctx.fillRect(0, 0, W, hH);
        ctx.fillStyle = A; ctx.fillRect(0, hH, W, 6);

        if (data.siteLogo) {
            const logo = await this.loadImage(data.siteLogo);
            ctx.save(); this.rr(ctx, 18, 8, 146, 146, 10); ctx.clip();
            this.drawImgContain(ctx, logo, 18, 8, 146, 146); ctx.restore();
        }

        const title = data.title || 'HOJE É O DIA PRA VENCER!';
        const [t1, ...trest] = title.split('!').map(s=>s.trim()).filter(Boolean);
        ctx.textAlign = 'left';
        ctx.font = `bold 48px 'Arial Black', Arial, sans-serif`;
        ctx.fillStyle = '#ffffff'; ctx.fillText(t1+'!', 182, 70);
        if (trest.length) {
            ctx.font = `bold 36px 'Arial Black', Arial, sans-serif`;
            ctx.fillStyle = A; ctx.fillText(trest.join('!')+( trest.length>0?'!':'' ), 182, 128);
        }

        /* — GAME ROWS — */
        const gStartY = hH + 12;
        const gTotalH  = H - gStartY - 85;
        const gH = games.length > 0 ? Math.floor((gTotalH - (games.length-1)*8) / games.length) : 0;

        for (let i = 0; i < games.length; i++) {
            const gY = gStartY + i*(gH+8);
            await this._gameRow(ctx, games[i], 14, gY, W-28, gH, A, i);
        }

        /* — FOOTER — */
        const fY = H-82;
        ctx.fillStyle = 'rgba(0,0,0,0.88)'; ctx.fillRect(0, fY, W, 82);
        ctx.fillStyle = A; ctx.fillRect(0, fY, W, 4);

        ctx.font = `bold 25px Arial, sans-serif`;
        ctx.fillStyle = '#ffffff'; ctx.textAlign = 'center';
        ctx.fillText(data.siteUrl||'', W*0.37, fY+50);

        if (data.instagram) {
            ctx.fillStyle = '#833ab4';
            this.rr(ctx, W*0.62, fY+14, 330, 52, 10); ctx.fill();
            ctx.font = `bold 21px Arial, sans-serif`;
            ctx.fillStyle = '#ffffff'; ctx.textAlign = 'center';
            ctx.fillText('📷 ' + data.instagram, W*0.62+165, fY+47);
        }

        return c;
    }

    async _gameRow(ctx, game, x, y, w, h, A, idx) {
        ctx.fillStyle = idx%2===0 ? 'rgba(0,0,0,0.72)' : 'rgba(5,25,8,0.72)';
        this.rr(ctx, x, y, w, h, 8); ctx.fill();
        ctx.fillStyle = A; this.rr(ctx, x, y, 6, h, 3); ctx.fill();

        const cY = y + h/2;
        const lr = Math.min(h*0.29, 42);
        const aX = x + 90 + lr, bX = x + w - 90 - lr;

        /* date top-center */
        ctx.font = `bold 14px Arial`; ctx.fillStyle = '#888888'; ctx.textAlign = 'center';
        ctx.fillText(game.dateTime||'', x+w/2, y+17);

        /* logos */
        const [imgA, imgB] = await Promise.all([
            this.loadImage(game.teamALogo),
            this.loadImage(game.teamBLogo)
        ]);
        [aX, bX].forEach(cx => {
            ctx.beginPath(); ctx.arc(cx, cY, lr+3, 0, Math.PI*2);
            ctx.fillStyle = 'rgba(255,255,255,0.06)'; ctx.fill();
        });
        this.drawImgCircle(ctx, imgA, aX, cY, lr);
        this.drawImgCircle(ctx, imgB, bX, cY, lr);

        /* team names */
        ctx.font = `bold ${Math.min(19, h*0.16)}px Arial`; ctx.fillStyle = '#ffffff';
        this.ellipsis(ctx, game.teamA||'', aX, cY+lr+18, 200);
        this.ellipsis(ctx, game.teamB||'', bX, cY+lr+18, 200);

        /* VS */
        ctx.font = `bold 38px 'Arial Black', Arial`; ctx.fillStyle = A; ctx.textAlign = 'center';
        ctx.fillText('VS', x+w/2, cY+10);

        /* odds chips */
        const chips = [
            {lbl:'C', val:game.oddHome},
            {lbl:'E', val:game.oddDraw},
            {lbl:'F', val:game.oddAway}
        ];
        const cW = 86, cH = Math.min(h*0.30, 38);
        const totW = chips.length*cW + (chips.length-1)*5;
        let cx2 = x+w/2-totW/2;
        const cYc = y + h - cH - 8;

        chips.forEach(ch => {
            ctx.fillStyle = 'rgba(0,0,0,0.55)';
            this.rr(ctx, cx2, cYc, cW, cH, 5); ctx.fill();
            ctx.strokeStyle = `${A}55`; ctx.lineWidth = 1;
            this.rr(ctx, cx2, cYc, cW, cH, 5); ctx.stroke();

            ctx.font = `10px Arial`; ctx.fillStyle = '#777'; ctx.textAlign = 'center';
            ctx.fillText(ch.lbl, cx2+cW/2, cYc+13);
            ctx.font = `bold 19px Arial`; ctx.fillStyle = A;
            ctx.fillText(ch.val||'-', cx2+cW/2, cYc+cH-7);
            cx2 += cW+5;
        });
    }

    /* ════════════════════════════════════════════════════════
       STORY BANNER — 1080×1920 (Instagram/WhatsApp Stories)
       Composição PREMIUM com assets reais (imagens PNG)
       ════════════════════════════════════════════════════════ */
    async generateStory(data, tpl = {}) {
        const W = 1080, H = 1920;
        const c = document.createElement('canvas');
        c.width = W; c.height = H;
        const ctx = c.getContext('2d');
        const A = tpl.accentColor || this._accent;
        const O = window.location.origin;
        const EL = O + '/img/banners/elements/';
        const BG = O + '/img/banners/backgrounds/';

        /* ─── Pré-carregar TODOS os assets em paralelo ─── */
        const [
            bgImg, vsImg, baseNameDark, baseNameGreen, baseFooter,
            frasesImg, iconSite, iconInsta, tituloImg,
            teamAImg, teamBImg, siteLogo
        ] = await Promise.all([
            this.loadImage(tpl.backgroundUrl || BG + 'fundo_estadio.png'),
            this.loadImage(EL + 'vs.png'),
            this.loadImage(EL + 'base_nomes_dark.png'),
            this.loadImage(EL + 'base_nomes_green.png'),
            this.loadImage(EL + 'base_footer.png'),
            this.loadImage(EL + 'frases_marketing.png'),
            this.loadImage(EL + 'icon_site.png'),
            this.loadImage(EL + 'icon_insta.png'),
            this.loadImage(EL + 'titulo_destaque.png'),
            this.loadImage(data.teamALogo),
            this.loadImage(data.teamBLogo),
            data.siteLogo ? this.loadImage(data.siteLogo) : Promise.resolve(null)
        ]);

        /* ═══ CAMADA 1: Fundo do estádio ═══ */
        ctx.drawImage(bgImg, 0, 0, W, H);

        /* Gradient overlay — escurece embaixo, mantém topo visível */
        const ovG = ctx.createLinearGradient(0, 0, 0, H);
        ovG.addColorStop(0, 'rgba(0,0,0,0.15)');
        ovG.addColorStop(0.35, 'rgba(0,0,0,0.30)');
        ovG.addColorStop(0.6, 'rgba(0,0,0,0.55)');
        ovG.addColorStop(1, 'rgba(0,0,0,0.85)');
        ctx.fillStyle = ovG;
        ctx.fillRect(0, 0, W, H);

        /* Vignette radial sutil */
        const vig = ctx.createRadialGradient(W/2, H*0.35, W*0.25, W/2, H*0.35, W*0.9);
        vig.addColorStop(0, 'rgba(0,0,0,0)');
        vig.addColorStop(1, 'rgba(0,0,0,0.35)');
        ctx.fillStyle = vig;
        ctx.fillRect(0, 0, W, H);

        /* ═══ CAMADA 2: Título 3D "DESTAQUE DE HOJE" (asset PNG) ═══ */
        if (tituloImg && tituloImg.width > 10) {
            const tW = 700, tH = tW * (tituloImg.height / tituloImg.width);
            ctx.save();
            ctx.shadowColor = 'rgba(0,0,0,0.6)'; ctx.shadowBlur = 20;
            this.drawImgContain(ctx, tituloImg, W/2 - tW/2, 30, tW, tH);
            ctx.restore();
        } else {
            /* Fallback: texto estilizado */
            ctx.save();
            ctx.font = `900 italic 78px 'Arial Black', Arial, sans-serif`;
            ctx.textAlign = 'center';
            ctx.shadowColor = 'rgba(0,0,0,0.8)'; ctx.shadowBlur = 18;
            ctx.shadowOffsetX = 4; ctx.shadowOffsetY = 4;
            /* Stroke dourado */
            ctx.strokeStyle = '#ffd700'; ctx.lineWidth = 3;
            ctx.strokeText(data.title || 'FAÇA SUA APOSTA', W/2, 110);
            ctx.fillStyle = '#ffffff';
            ctx.fillText(data.title || 'FAÇA SUA APOSTA', W/2, 110);
            ctx.restore();
        }

        /* ═══ CAMADA 3: Logo do site com glow ═══ */
        if (siteLogo) {
            const lW = 240, lH = 240;
            ctx.save();
            ctx.shadowColor = A; ctx.shadowBlur = 35;
            this.drawImgContain(ctx, siteLogo, W/2-lW/2, 240, lW, lH);
            ctx.restore();
        }

        /* ═══ CAMADA 4: Escudos dos times — GRANDES com glow ═══ */
        const tY = 680;
        const logoSize = 300;
        const aX = 60;
        const bX = W - 60 - logoSize;

        /* Halo atrás de cada escudo */
        [aX + logoSize/2, bX + logoSize/2].forEach(cx => {
            const halo = ctx.createRadialGradient(cx, tY, 0, cx, tY, logoSize*0.55);
            halo.addColorStop(0, `${A}30`);
            halo.addColorStop(0.6, `${A}10`);
            halo.addColorStop(1, 'rgba(0,0,0,0)');
            ctx.fillStyle = halo;
            ctx.fillRect(cx - logoSize*0.7, tY - logoSize*0.7, logoSize*1.4, logoSize*1.4);
        });

        ctx.save();
        ctx.shadowColor = 'rgba(0,0,0,0.7)'; ctx.shadowBlur = 30;
        this.drawImgContain(ctx, teamAImg, aX, tY - logoSize/2, logoSize, logoSize);
        this.drawImgContain(ctx, teamBImg, bX, tY - logoSize/2, logoSize, logoSize);
        ctx.restore();

        /* ═══ CAMADA 5: VS (imagem PNG) com glow ═══ */
        const vsW = 160, vsH = 200;
        ctx.save();
        ctx.shadowColor = '#ffffff'; ctx.shadowBlur = 25;
        this.drawImgContain(ctx, vsImg, W/2-vsW/2, tY-vsH/2, vsW, vsH);
        ctx.restore();

        /* ═══ CAMADA 6: Liga + Horário badge ═══ */
        const leagueText = (data.league || '') + '  •  ' + (data.dateTime || '');
        const badgeY = 880;
        /* Pill background */
        const pillW = ctx.measureText ? 600 : 600;
        ctx.fillStyle = 'rgba(0,0,0,0.6)';
        this.rr(ctx, W/2-pillW/2, badgeY - 28, pillW, 48, 24); ctx.fill();
        ctx.strokeStyle = `${A}88`; ctx.lineWidth = 1.5;
        this.rr(ctx, W/2-pillW/2, badgeY - 28, pillW, 48, 24); ctx.stroke();

        ctx.font = `bold 28px Arial, sans-serif`;
        ctx.fillStyle = '#ffffff'; ctx.textAlign = 'center';
        ctx.fillText(leagueText.toUpperCase(), W/2, badgeY + 2);

        /* ═══ CAMADA 7: Barra verde com nomes dos times ═══ */
        const barY = 940;
        const barW = 950, barH = 90;
        ctx.drawImage(baseNameGreen, W/2-barW/2, barY, barW, barH);

        const matchText = (data.teamA || '') + '  X  ' + (data.teamB || '');
        ctx.font = `900 46px 'Arial Black', Arial, sans-serif`;
        ctx.fillStyle = '#ffffff'; ctx.textAlign = 'center';
        ctx.save();
        ctx.shadowColor = 'rgba(0,0,0,0.6)'; ctx.shadowBlur = 8;
        this.ellipsis(ctx, matchText.toUpperCase(), W/2, barY + 62, barW - 50);
        ctx.restore();

        /* ═══ CAMADA 8: Odds — estilo PREMIUM com glassmorphism ═══ */
        const oY = 1075;
        const odds = [
            { val: data.oddHome||'-', lbl: 'CASA',   x: W*0.18 },
            { val: data.oddDraw||'-', lbl: 'EMPATE', x: W*0.50 },
            { val: data.oddAway||'-', lbl: 'FORA',   x: W*0.82 },
        ];
        const chipW = 240, chipH = 140;

        odds.forEach(o => {
            const cx = o.x, cy = oY + chipH/2;
            /* Glass background */
            ctx.fillStyle = 'rgba(0,0,0,0.50)';
            this.rr(ctx, cx - chipW/2, oY, chipW, chipH, 16); ctx.fill();
            ctx.strokeStyle = `${A}55`; ctx.lineWidth = 1.5;
            this.rr(ctx, cx - chipW/2, oY, chipW, chipH, 16); ctx.stroke();

            /* Label */
            ctx.font = `bold 18px Arial, sans-serif`;
            ctx.fillStyle = 'rgba(255,255,255,0.5)';
            ctx.textAlign = 'center';
            ctx.fillText(o.lbl, cx, oY + 30);

            /* Valor com glow */
            ctx.font = `900 68px 'Arial Black', Arial, sans-serif`;
            ctx.fillStyle = A;
            ctx.save();
            ctx.shadowColor = A; ctx.shadowBlur = 20;
            ctx.fillText(o.val, cx, oY + 105);
            ctx.restore();
        });

        /* ═══ CAMADA 9: Frases de marketing ═══ */
        const frasesW = 900, frasesH = 130;
        ctx.save();
        ctx.shadowColor = 'rgba(0,0,0,0.5)'; ctx.shadowBlur = 10;
        ctx.drawImage(frasesImg, W/2-frasesW/2, 1260, frasesW, frasesH);
        ctx.restore();

        /* ═══ CAMADA 10: Footer PREMIUM com URL e Instagram ═══ */
        const fY = 1460;
        const fbW = 850, fbH = 75;

        /* Barra Site */
        ctx.drawImage(baseFooter, W/2-fbW/2, fY, fbW, fbH);
        ctx.drawImage(iconSite, W/2-fbW/2+18, fY+12, 50, 50);
        ctx.font = `bold 30px Arial, sans-serif`;
        ctx.fillStyle = '#ffffff'; ctx.textAlign = 'center';
        const siteUrlClean = (data.siteUrl || '').replace(/^https?:\/\//, '').replace(/\/$/, '');
        ctx.fillText('WWW.' + siteUrlClean.toUpperCase(), W/2+20, fY+50);

        /* Barra Instagram */
        if (data.instagram) {
            const igY = fY + 90;
            ctx.drawImage(baseFooter, W/2-fbW/2, igY, fbW, fbH);
            ctx.drawImage(iconInsta, W/2-fbW/2+18, igY+12, 50, 50);
            ctx.font = `bold 30px Arial, sans-serif`;
            ctx.fillStyle = '#ffffff'; ctx.textAlign = 'center';
            ctx.fillText(data.instagram, W/2+20, igY+50);
        }

        /* ═══ CAMADA 11: Linha decorativa de rodapé ═══ */
        const lineY = H - 80;
        ctx.fillStyle = A;
        ctx.fillRect(W/2 - 200, lineY, 400, 3);
        ctx.font = `11px Arial, sans-serif`;
        ctx.fillStyle = 'rgba(255,255,255,0.25)'; ctx.textAlign = 'center';
        ctx.fillText('Aposte com responsabilidade  •  +18', W/2, lineY + 25);

        return c;
    }



    /* ── output ─────────────────────────────────────────── */

    download(canvas, filename = 'banner.png') {
        canvas.toBlob(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = filename; a.click();
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        }, 'image/png');
    }

    toDataURL(canvas) { return canvas.toDataURL('image/png'); }

    previewTo(canvas, targetImg) { targetImg.src = this.toDataURL(canvas); }
}

/* singleton global — usable as window.BannerEngine.generateStory(...) */
window.BannerEngine = new BannerEngine();
