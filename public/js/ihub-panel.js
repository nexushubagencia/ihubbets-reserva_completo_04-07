/**
 * ⚡ IHUB Panel Core JS
 * Gerencia o modo dark/light e inicializa componentes dinâmicos.
 */

(function() {
    // 1. Aplicação IMEDIATA do Dark Mode (evita flash branco)
    const theme = localStorage.getItem('ihub_theme') || 'light';
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
        // Ajusta classes do navbar e sidebar se necessário
        const nav = document.querySelector('.main-header');
        if (nav) {
            nav.classList.remove('navbar-light');
            nav.classList.add('navbar-dark');
        }
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        // 2. Toggle do Modo Escuro
        const toggleBtn = document.querySelector('#dark-mode-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                localStorage.setItem('ihub_theme', isDark ? 'dark' : 'light');
                
                // Atualiza cores do layout AdminLTE
                const nav = document.querySelector('.main-header');
                if (nav) {
                    nav.classList.toggle('navbar-light');
                    nav.classList.toggle('navbar-dark');
                }
            });
        }

        // 3. Inicialização de Gráficos (Dashboard)
        const ctx = document.getElementById('performanceChart');
        if (ctx && window.chartData) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: window.chartData.labels,
                    datasets: [
                        {
                            label: 'Entradas',
                            backgroundColor: 'rgba(60,141,188,0.2)',
                            borderColor: 'rgba(60,141,188,0.8)',
                            data: window.chartData.entradas,
                            fill: true
                        },
                        {
                            label: 'Saídas (Prêmios)',
                            backgroundColor: 'rgba(210, 214, 222, 0.2)',
                            borderColor: 'rgba(210, 214, 222, 1)',
                            data: window.chartData.saidas,
                            fill: true
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: { display: true },
                    scales: {
                        xAxes: [{ gridLines: { display: false } }],
                        yAxes: [{ gridLines: { display: true } }]
                    }
                }
            });
        }
    });
})();
