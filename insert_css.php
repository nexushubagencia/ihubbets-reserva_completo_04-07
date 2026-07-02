<?php
$content = file_get_contents('public/dist/css/custom.css');
$insert = <<< 'EOT'

/* ══════════════════════════════════════════════════════════════════
   FIX MODAIS IHUB - CORREÇÃO DEFINITIVA DO STACKING CONTEXT
   ══════════════════════════════════════════════════════════════════
   CAUSA RAIZ: O AdminLTE define .wrapper com position:relative,
   o que cria um "stacking context" isolado. Bootstrap coloca o
   .modal-backdrop direto no <body> (FORA do .wrapper), enquanto
   os modais do Vue ficam DENTRO do .wrapper. Resultado: nenhum 
   z-index nos modais consegue ultrapassar o backdrop, porque 
   eles estão em contextos de empilhamento diferentes.
   
   SOLUÇÃO: Neutralizar o stacking context do .wrapper quando 
   body tem a classe .modal-open (que o Bootstrap adiciona).
   ══════════════════════════════════════════════════════════════════ */

/* 1. NEUTRALIZA O STACKING CONTEXT QUANDO MODAL ESTÁ ABERTO */
body.modal-open .wrapper {
    position: static !important;
    overflow: visible !important;
    transform: none !important;
    -webkit-transform: none !important;
    filter: none !important;
    -webkit-filter: none !important;
}

body.modal-open .content-wrapper {
    z-index: auto !important;
    transform: none !important;
    -webkit-transform: none !important;
}

body.modal-open .main-header {
    z-index: auto !important;
}

body.modal-open .main-sidebar {
    z-index: auto !important;
    transform: none !important;
    -webkit-transform: none !important;
}

/* 3. FIX DEFINITIVO PARA MODAIS ADMINLTE */
/* Quando o modal abre, anulamos qualquer stacking context dos pais */
body.modal-open .wrapper, 
body.modal-open .content-wrapper,
body.modal-open .content,
body.modal-open section.content {
    position: static !important;
    overflow: visible !important;
    transform: none !important;
    perspective: none !important;
    z-index: auto !important;
    filter: none !important;
}

/* 🚩 REBAIXAR NAVBAR E SIDEBAR (Garante que fiquem ATRÁS do modal) */
body.modal-open .main-header,
body.modal-open .main-sidebar,
body.modal-open .left-side {
    z-index: 100 !important; 
}

/* Camadas de profundidade */
.modal-backdrop {
    z-index: 9998 !important;
    background-color: rgba(0, 0, 0, 0.8) !important;
    opacity: 1 !important;
}

.modal {
    z-index: 10000 !important;
    pointer-events: auto !important;
    overflow-y: auto !important;
    padding-top: 50px !important; /* Força um espaço inicial */
}

.modal {
    z-index: 10000 !important;
}

.modal-backdrop {
    z-index: 9999 !important;
}

/* 🚩 CENTRALIZAÇÃO E RESPIRO (Nível Senior) */
.modal-dialog {
    z-index: 10001 !important;
    margin: 1.75rem auto !important;
    top: 10vh !important; /* Move o modal para baixo do cabeçalho do site */
}

/* Ajuste específico para os modais principais (Nível Senior) */
[id^="modal-"] .modal-dialog {
    top: 8vh !important;
}

/* 🚩 RESPONSIVIDADE MOBILE PARA MODAIS (Nível Senior) */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px auto !important;
        width: 95% !important;
        top: 60px !important; /* Ajuste fixo para mobile para garantir que o X apareça */
    }
}

/* Garante que o botão de fechar (X) seja visível e tenha contraste */
.modal-header .close {
    opacity: 0.8 !important;
    text-shadow: none !important;
    color: #000 !important;
    font-size: 30px !important;
    z-index: 10002 !important;
    position: relative;
}

.modal-header {
    z-index: 10002 !important;
}

/* Estado de Exibição */
.modal.in, 
.modal.show {
    display: block !important;
    opacity: 1 !important;
}

.modal-content {
    background-color: #fff !important;
    box-shadow: 0 25px 80px rgba(0,0,0,0.8) !important;
    pointer-events: auto !important;
    border: none !important;
    border-radius: 12px !important;
}

/* Limpeza de scroll */
body.modal-open {
    overflow: hidden !important;
}

#modal-cupon .modal-dialog {
    margin: 10px auto;
}

#modal-cupon .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* 7. PIN modal sempre acima de tudo */
#modal-pre-aposta {
    z-index: 200000 !important;
}

#modal-validar-pin {
    z-index: 1070 !important;
}

EOT;

$content = str_replace("    display: none !important;\n}\n\n/* Divisor de Ticket com efeito de corte lateral */", "    display: none !important;\n}" . $insert . "\n\n/* Divisor de Ticket com efeito de corte lateral */", $content);
file_put_contents('public/dist/css/custom.css', $content);
echo "Done.";
