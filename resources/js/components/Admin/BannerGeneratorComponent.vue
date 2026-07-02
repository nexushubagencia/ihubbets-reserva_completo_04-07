<template>
  <div class="card card-dark card-outline shadow-lg">
    <div class="card-header border-0 bg-dark text-white py-3">
      <h3 class="card-title text-uppercase fw-bold"><i class="fas fa-magic me-2 text-warning"></i> Estúdio de Artes (1080x1920)</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4">
           <div class="form-group mb-3">
              <label class="fw-bold"><i class="fas fa-image me-1"></i> Escolher Fundo (Background)</label>
              <div class="d-flex gap-2 flex-wrap mt-2">
                 <div class="bg-item" :class="{active: selectedBg === 'default'}" @click="setBg('default')">Padrão</div>
                 <div v-for="bg in backgrounds" :key="bg.id" class="bg-item" :class="{active: selectedBg === bg.url}" @click="setBg(bg.url)">
                    {{ bg.name }}
                 </div>
                 <div class="bg-item add-new" @click="triggerUpload"><i class="fas fa-plus"></i></div>
              </div>
              <input type="file" ref="fileInput" class="d-none" @change="uploadBackground">
           </div>

           <div class="form-group mb-3">
              <label class="fw-bold"><i class="fas fa-calendar-alt me-1"></i> Confronto Principal</label>
              <select class="form-select" v-model="selectedMatchId" @change="updatePreview">
                 <option value="">Escolha um jogo...</option>
                 <option v-for="match in matches" :key="match.id" :value="match.id">
                    {{ match.home_team }} vs {{ match.away_team }}
                 </option>
              </select>
           </div>
           
           <div class="form-group mb-3">
              <label class="fw-bold">Manchete Superior</label>
              <input type="text" class="form-control" v-model="bannerTitle" @input="updatePreview">
           </div>

           <div class="form-group mb-3">
              <label class="fw-bold">Rodapé (Insta/Site)</label>
              <input type="text" class="form-control" v-model="bannerFooter" @input="updatePreview">
           </div>

           <hr>
           <button class="btn btn-success btn-lg w-100 mb-3" @click="downloadBanner">
              <i class="fas fa-cloud-download-alt me-2"></i> Baixar Arte para Stories
           </button>
        </div>

        <div class="col-md-8 bg-gray-dark p-4 rounded d-flex justify-content-center border" style="max-height: 800px; overflow-y: auto;">
            <div class="canvas-wrapper shadow-2xl" style="width: 360px; height: 640px;">
                <canvas ref="bannerCanvas" width="1080" height="1920" style="width: 100%; height: 100%; border-radius: 20px;"></canvas>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';

const matches = ref([
    {id: 1, home_team: 'Flamengo', away_team: 'Palmeiras', odd_home: '1.95', odd_draw: '3.40', odd_away: '4.10'},
    {id: 2, home_team: 'Real Madrid', away_team: 'Barcelona', odd_home: '2.10', odd_draw: '3.60', odd_away: '3.20'}
]);

const backgrounds = ref([]);
const selectedBg = ref('default');
const selectedMatchId = ref('');
const bannerTitle = ref('JOGO DO DIA');
const bannerFooter = ref('@IHUB_BETS');
const bannerCanvas = ref(null);
const fileInput = ref(null);

const setBg = (url) => {
    selectedBg.value = url;
    updatePreview();
};

const triggerUpload = () => fileInput.value.click();

const updatePreview = () => {
    const canvas = bannerCanvas.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const match = matches.value.find(m => m.id == selectedMatchId.value) || { home_team: 'TIME A', away_team: 'TIME B', odd_home: '0.00', odd_draw: '0.00', odd_away: '0.00' };

    // Fundo Principal
    if (selectedBg.value === 'default') {
        const grad = ctx.createLinearGradient(0, 0, 0, 1920);
        grad.addColorStop(0, '#111827');
        grad.addColorStop(1, '#065f46');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 1080, 1920);
    } else {
        // Carregar imagem de fundo personalizada se existisse
        ctx.fillStyle = "#000";
        ctx.fillRect(0, 0, 1080, 1920);
    }

    // Header Glow
    ctx.shadowBlur = 50;
    ctx.shadowColor = "#10b981";
    ctx.fillStyle = "rgba(16, 185, 129, 0.2)";
    ctx.fillRect(0, 0, 1080, 400);
    ctx.shadowBlur = 0;

    // Título Principal (Story Style)
    ctx.font = "bold 80px Inter, Montserrat, sans-serif";
    ctx.fillStyle = "#ffffff";
    ctx.textAlign = "center";
    ctx.fillText(bannerTitle.value.toUpperCase(), 540, 300);

    // Divisor Neon
    ctx.fillStyle = "#10b981";
    ctx.fillRect(340, 340, 400, 8);

    // Confronto Teams
    ctx.font = "bold 120px Inter, sans-serif";
    ctx.fillStyle = "#ffffff";
    ctx.fillText(match.home_team, 540, 650);
    
    ctx.font = "bold 60px Inter, sans-serif";
    ctx.fillStyle = "#10b981";
    ctx.fillText("VS", 540, 750);

    ctx.font = "bold 120px Inter, sans-serif";
    ctx.fillStyle = "#ffffff";
    ctx.fillText(match.away_team, 540, 880);

    // Odds Section
    drawMobileOdd(ctx, 540, 1100, "VITORIA " + match.home_team, match.odd_home);
    drawMobileOdd(ctx, 540, 1300, "EMPATE", match.odd_draw);
    drawMobileOdd(ctx, 540, 1500, "VITORIA " + match.away_team, match.odd_away);

    // Footer
    ctx.font = "600 50px Inter, sans-serif";
    ctx.fillStyle = "rgba(255,255,255,0.8)";
    ctx.fillText(bannerFooter.value, 540, 1800);
};

const drawMobileOdd = (ctx, x, y, label, val) => {
    ctx.fillStyle = "rgba(255,255,255,0.05)";
    ctx.beginPath();
    ctx.roundRect(x - 450, y - 80, 900, 160, 30);
    ctx.fill();
    ctx.strokeStyle = "rgba(16, 185, 129, 0.5)";
    ctx.stroke();

    ctx.textAlign = "left";
    ctx.font = "bold 45px Inter, sans-serif";
    ctx.fillStyle = "#ffffff";
    ctx.fillText(label, x - 400, y + 15);

    ctx.textAlign = "right";
    ctx.fillStyle = "#10b981";
    ctx.font = "bold 70px Inter, sans-serif";
    ctx.fillText(val, x + 400, y + 25);
    ctx.textAlign = "center";
};

const downloadBanner = () => {
    const link = document.createElement('a');
    link.download = `story-ihub-${Date.now()}.png`;
    link.href = bannerCanvas.value.toDataURL();
    link.click();
};

onMounted(() => {
    nextTick(() => updatePreview());
});
</script>

<style scoped>
.bg-item { width: 80px; height: 80px; background: #333; border: 2px solid transparent; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #fff; font-size: 10px; text-align: center; }
.bg-item.active { border-color: #10b981; background: #065f46; }
.bg-item.add-new { border: 2px dashed #666; background: transparent; font-size: 20px; }
.canvas-wrapper { border: 10px solid #222; border-radius: 30px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
</style>
