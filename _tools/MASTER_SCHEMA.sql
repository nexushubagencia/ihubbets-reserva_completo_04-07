-- ############################################################
-- IHUB BETS V2 - MASTER ARCHITECTURE SCHEMA (MARCO ZERO)
-- Arquitetura Multi-Tenant / State-of-the-Art
-- ############################################################

-- 1. SISTEMA DE BANCA (WHITE-LABEL)
CREATE TABLE sites (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    complete_name VARCHAR(255),
    first_name VARCHAR(100),
    second_name VARCHAR(100),
    domain VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('active', 'suspended', 'pending') DEFAULT 'active',
    max_sellers INT DEFAULT 10,
    due_day INT DEFAULT 10,
    app_version VARCHAR(20) DEFAULT '2.0.0',

    -- LAYOUT (Aba Configurações > Layout)
    theme_color VARCHAR(50) DEFAULT 'verde-claro',
    theme_mode ENUM('dark', 'light', 'custom') DEFAULT 'dark',
    active_custom_colors TINYINT DEFAULT 0,
    primary_color VARCHAR(20) DEFAULT '#00ff00',
    secondary_color VARCHAR(20) DEFAULT '#000000',
    sidebar_color VARCHAR(20) DEFAULT '#173133',
    container_jogos_color VARCHAR(20) DEFAULT '#35aa71',
    logo_color VARCHAR(20) DEFAULT '#329d6a',
    btn_color VARCHAR(20) DEFAULT '#1aa6d0',
    btn_cef_color VARCHAR(20) DEFAULT 'rgb(30,38,42)',
    fundo_color VARCHAR(20) DEFAULT '#dddddd',
    linhas_color VARCHAR(20) DEFAULT '#1aa6d0',
    btn_selecionado_color VARCHAR(20) DEFAULT 'rgb(41,118,163)',
    hover_menu_color VARCHAR(20) DEFAULT '#339063',
    btn_salvar_hover_color VARCHAR(20) DEFAULT '#1093bb',
    logo_path TEXT,
    favicon_path TEXT,
    background_img TEXT,

    -- LAYOUT: Carrossel e Redes Sociais
    carrosel_ativado TINYINT DEFAULT 1,
    texto_rodape_bilhete TEXT,
    whatsapp_country_code VARCHAR(5) DEFAULT '+55',
    whatsapp_number VARCHAR(20),
    whatsapp_suporte_country_code VARCHAR(5) DEFAULT '+55',
    whatsapp_suporte_number VARCHAR(20),
    social_facebook TEXT,
    social_youtube TEXT,
    social_instagram TEXT,
    advanced_sharing TINYINT DEFAULT 1,

    -- LAYOUT: Moeda e Idioma
    prefixo_moeda VARCHAR(10) DEFAULT 'R$',
    language VARCHAR(10) DEFAULT 'pt-br',
    site_template VARCHAR(50) DEFAULT 'default',

    -- INTEGRAÇÕES
    payment_gateway VARCHAR(50) DEFAULT 'mercado_pago',
    pix_module TINYINT DEFAULT 0,
    pix_status TINYINT DEFAULT 0,
    meta_pixel_id VARCHAR(100),
    active_meta_pixel TINYINT DEFAULT 0,

    -- MÓDULOS ATIVÁVEIS
    active_sports TINYINT DEFAULT 1,
    active_casino TINYINT DEFAULT 0,
    active_financial_manager TINYINT DEFAULT 0,
    active_online_user TINYINT DEFAULT 0,
    active_new_ticket_realtime TINYINT DEFAULT 0,
    active_notifications TINYINT DEFAULT 1,
    active_seller_user TINYINT DEFAULT 1,

    -- EXIBIÇÃO DO SITE (Esportes)
    display_modalities VARCHAR(50) DEFAULT 'sports',
    op_futebol ENUM('Sim', 'Nao') DEFAULT 'Sim',
    op_volei ENUM('Sim', 'Nao') DEFAULT 'Sim',
    op_basquete ENUM('Sim', 'Nao') DEFAULT 'Sim',
    op_tenis ENUM('Sim', 'Nao') DEFAULT 'Sim',
    op_ufcbox ENUM('Sim', 'Nao') DEFAULT 'Sim',
    op_outras_loterias ENUM('Sim', 'Nao') DEFAULT 'Nao',
    op_hoje ENUM('sim', 'nao') DEFAULT 'sim',
    op_amanha ENUM('sim', 'nao') DEFAULT 'sim',
    op_depois_amanha ENUM('sim', 'nao') DEFAULT 'sim',
    op_aovivo ENUM('sim', 'nao') DEFAULT 'sim',

    -- APK
    apk_name VARCHAR(255),

    -- E-MAIL SMTP
    mail_driver VARCHAR(20) DEFAULT 'smtp',
    mail_host VARCHAR(255) DEFAULT 'smtp.gmail.com',
    mail_port VARCHAR(10) DEFAULT '465',
    mail_username VARCHAR(255),
    mail_password VARCHAR(255),
    mail_encryption VARCHAR(10) DEFAULT 'ssl',
    mail_from_address VARCHAR(255),
    mail_name VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 1.1 CONFIGURAÇÕES DE APOSTAS POR SITE (Aba Configurações)
CREATE TABLE site_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNIQUE NOT NULL,

    -- EVENTOS PRÉ-JOGO
    limite_apostas_iguais INT DEFAULT 0,
    valor_mini_aposta DECIMAL(10,2) DEFAULT 1.00,
    valor_max_aposta DECIMAL(10,2) DEFAULT 1000.00,
    premio_max DECIMAL(15,2) DEFAULT 50000.00,
    cotacao_mini_bilhete DECIMAL(10,2) DEFAULT 1.40,
    cotacao_max_bilhete DECIMAL(10,2) DEFAULT 1000.00,
    quantidade_jogos_mini_bilhete INT DEFAULT 1,
    quantidade_jogos_max_bilhete INT DEFAULT 12,
    quantidade_times_visitantes_mesmo_camp INT DEFAULT 5,
    bloquear_odd_abaixo DECIMAL(10,2) DEFAULT 1.00,
    travar_odd_acima DECIMAL(10,2) DEFAULT 100.00,
    mesclar_apostas TINYINT DEFAULT 1,

    -- EVENTOS PRÉ-JOGO (MÚLTIPLA)
    cotacao_mini_bilhete_mult DECIMAL(10,2),
    cotacao_max_bilhete_mult DECIMAL(10,2),
    valor_mini_aposta_mult DECIMAL(10,2),
    valor_max_aposta_mult DECIMAL(10,2),
    premio_max_mult DECIMAL(15,2),

    -- EVENTOS AO VIVO
    futebol_ao_vivo ENUM('Sim', 'Nao') DEFAULT 'Nao',
    limite_tempo_aovivo INT DEFAULT 90,
    live_quantidade_jogos_mini_bilhete INT DEFAULT 1,
    live_quantidade_jogos_max_bilhete INT DEFAULT 10,
    live_valor_mini_aposta DECIMAL(10,2) DEFAULT 2.00,
    live_valor_max_aposta DECIMAL(10,2) DEFAULT 500.00,
    live_premio_max DECIMAL(15,2) DEFAULT 10000.00,
    live_cotacao_mini_bilhete DECIMAL(10,2) DEFAULT 2.00,
    live_cotacao_max_bilhete DECIMAL(10,2) DEFAULT 1000.00,
    live_cotacao_mini_gerar_comissao DECIMAL(10,2) DEFAULT 2.00,
    live_maximo_cotacao DECIMAL(10,2) DEFAULT 100.00,
    percentage_live DECIMAL(5,2) DEFAULT 0.00,

    -- EVENTOS AO VIVO (MÚLTIPLA)
    live_cotacao_mini_bilhete_mult DECIMAL(10,2),
    live_cotacao_max_bilhete_mult DECIMAL(10,2),
    live_valor_mini_aposta_mult DECIMAL(10,2),
    live_valor_max_aposta_mult DECIMAL(10,2),
    live_premio_max_mult DECIMAL(15,2),

    -- GERAL
    aposta_ativa ENUM('Sim', 'Nao') DEFAULT 'Sim',
    bloq_aposta_madrugada ENUM('Sim', 'Nao') DEFAULT 'Sim',
    data_limite_jogos DATE DEFAULT '2050-06-19',
    hours_limit_date TIME DEFAULT '23:59:59',
    minutos_antes_inicio INT DEFAULT 0,
    pin_validation_minutes INT DEFAULT 500,
    pin_unico TINYINT DEFAULT 0,
    pin_update_config TINYINT DEFAULT 0,
    modo_listagem VARCHAR(50) DEFAULT 'todos',
    op_app_cliente TINYINT DEFAULT 1,
    request_document TINYINT DEFAULT 1,
    term_accepted TINYINT DEFAULT 0,

    -- ALERTAS
    alerta_aposta_acima DECIMAL(10,2) DEFAULT 50.00,

    -- PERMISSÕES
    cambista_pode_cancelar ENUM('Sim', 'Nao') DEFAULT 'Sim',
    tempo_limite_camb_cancela_aposta INT DEFAULT 5,
    cambista_paga TINYINT DEFAULT 0,
    percentual_paga DECIMAL(5,2) DEFAULT 0.00,
    gerente_cancela TINYINT DEFAULT 1,
    gerente_remove_cambista TINYINT DEFAULT 1,
    gerente_edita_cambista TINYINT DEFAULT 1,
    gerente_cria_cambista TINYINT DEFAULT 1,

    -- INTEGRAÇÕES
    cda_code INT,
    cda_status TINYINT DEFAULT 0,
    pix_mode VARCHAR(20) DEFAULT 'checkbox',
    bankizi_status TINYINT DEFAULT 0,

    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- 1.2 PÁGINAS DE CONTEÚDO POR SITE (Aba Personalização)
CREATE TABLE site_pages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    page_type ENUM('regulamento', 'sobre_nos', 'compartilhamentos', 'termos') NOT NULL,
    title VARCHAR(255),
    content LONGTEXT, -- HTML ou Markdown
    status TINYINT DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY (site_id, page_type),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- 2. USUÁRIOS E PERMISSÕES (ESTRUTURA HIERÁRQUICA)
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT, -- A qual banca este usuário pertence (NULL se for SuperAdmin)
    name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'manager', 'seller', 'client') NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    pix_key VARCHAR(255),
    pix_type ENUM('cpf', 'email', 'phone', 'random'),
    status TINYINT DEFAULT 1, -- 1: Active, 0: Blocked
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- 3. CARTEIRA E SISTEMA DE BÔNUS (WALLLET)
CREATE TABLE wallets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    balance_real DECIMAL(15,2) DEFAULT 0.00,
    balance_bonus DECIMAL(15,2) DEFAULT 0.00,
    rollover_met DECIMAL(15,2) DEFAULT 0.00, -- Monitoramento do bônus de boas-vindas
    rollover_target DECIMAL(15,2) DEFAULT 0.00,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. EVENTOS MANUAIS (VAQUEJADA / X1)
CREATE TABLE manual_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL -- Ex: Vaquejada, X1, BBB
);

CREATE TABLE manual_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT,
    site_id BIGINT,
    title VARCHAR(255) NOT NULL, -- Ex: Vaquejada de Serrinha
    start_time DATETIME NOT NULL,
    status ENUM('open', 'finished', 'cancelled') DEFAULT 'open',
    
    FOREIGN KEY (category_id) REFERENCES manual_categories(id),
    FOREIGN KEY (site_id) REFERENCES sites(id)
);

CREATE TABLE manual_markets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT,
    name VARCHAR(255) NOT NULL, -- Ex: Vencedor da Corrida, Boi na Faixa
    description TEXT,
    
    FOREIGN KEY (event_id) REFERENCES manual_events(id) ON DELETE CASCADE
);

CREATE TABLE manual_odds (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    market_id BIGINT,
    label VARCHAR(255) NOT NULL, -- Ex: Dupla A, Dupla B
    value DECIMAL(10,2) NOT NULL,
    is_winner TINYINT DEFAULT 0, -- Definido pelo Admin no fim do evento
    
    FOREIGN KEY (market_id) REFERENCES manual_markets(id) ON DELETE CASCADE
);

-- 5. TRANSAÇÕES PIX (GATEWAY INTEGRATION)
CREATE TABLE transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    type ENUM('deposit', 'withdraw', 'bet_payout', 'bet_placed', 'bonus_credit') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    gateway_ref VARCHAR(255), -- ID do Mercado Pago/SuitPay
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 7. SISTEMA DE APOSTAS (BET ENGINE)
CREATE TABLE bets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL, -- Cliente ou Cambista
    external_code VARCHAR(20) UNIQUE, -- PIN do bilhete
    type ENUM('simple', 'multiple') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    potential_payout DECIMAL(15,2) NOT NULL,
    
    -- Cash Out Logic
    status ENUM('open', 'won', 'lost', 'cancelled', 'cashed_out') DEFAULT 'open',
    cash_out_amount DECIMAL(15,2) DEFAULT NULL,
    can_cash_out TINYINT DEFAULT 1, -- Admin pode travar o cashout se quiser
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE bet_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bet_id BIGINT NOT NULL,
    match_id BIGINT NOT NULL, -- ID da BetsAPI ou Manual Event
    league_name VARCHAR(255),
    home_team VARCHAR(255),
    away_team VARCHAR(255),
    market_name VARCHAR(255), -- Ex: Vencedor
    selection_label VARCHAR(255), -- Ex: Time A
    selection_odd DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'won', 'lost', 'cancelled') DEFAULT 'pending',
    
    FOREIGN KEY (bet_id) REFERENCES bets(id) ON DELETE CASCADE
);

-- 6. SISTEMA DE AFILIADOS E COMISSÕES
CREATE TABLE affiliate_relations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    referrer_id BIGINT NOT NULL, -- O usuário que indicou
    referred_id BIGINT NOT NULL, -- O novo usuário
    commission_type ENUM('faturamento', 'puxada', 'cpa') DEFAULT 'faturamento',
    commission_value DECIMAL(10,2), -- Ex: 10%
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (referrer_id) REFERENCES users(id),
    FOREIGN KEY (referred_id) REFERENCES users(id)
);

CREATE TABLE affiliate_payouts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    affiliate_id BIGINT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (affiliate_id) REFERENCES users(id)
);

-- 8. GESTÃO DE CONTEÚDO E BANNERS (WHITE-LABEL)
CREATE TABLE banners (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    title VARCHAR(255),
    image_path TEXT NOT NULL,
    link_url TEXT,
    position ENUM('home_main', 'sidebar', 'footer', 'popup') DEFAULT 'home_main',
    order_index INT DEFAULT 0,
    status TINYINT DEFAULT 1, -- 1: Ativo, 0: Inativo
    
    start_date DATETIME,
    end_date DATETIME,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- 9. SEGURANÇA E AUDITORIA (BLINDAGEM)
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT,
    user_id BIGINT,
    action VARCHAR(255) NOT NULL, -- Ex: 'UPDATE_ODD', 'CANCEL_BET', 'WITHDRAW_APPROVE'
    target_type VARCHAR(100), -- Ex: 'manual_odds', 'bets'
    target_id BIGINT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 10. CONFIGURAÇÕES AVANÇADAS DO SISTEMA
CREATE TABLE system_configs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT,
    config_key VARCHAR(100) NOT NULL,
    config_value TEXT,
    
    UNIQUE KEY (site_id, config_key),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- 11. CONFIGURAÇÃO DE MERCADOS AO VIVO (BetsAPI)
CREATE TABLE live_market_configs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id VARCHAR(50) NOT NULL,
    match_id BIGINT, -- NULL = config global, com valor = config específica
    market_name VARCHAR(100) NOT NULL,
    percentage DECIMAL(5,2) DEFAULT 0.00, -- Margem do admin sobre a odd
    status TINYINT DEFAULT 1,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Mercados padrão do sistema:
-- fulltime_result, result_/_both_teams_to_score, both_teams_to_score,
-- half_time/full_time, half_time_double_chance, double_chance,
-- half_time_correct_score, half_time_result, goals_odd/even,
-- final_score, draw_no_bet, to_win_2nd_half,
-- both_teams_to_score_in_1st_half, both_teams_to_score_in_2nd_half,
-- total_corners

-- 12. CONTROLE DE LIGAS E PARTIDAS BLOQUEADAS
CREATE TABLE blocked_leagues (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    league_name VARCHAR(255) NOT NULL,
    sport VARCHAR(50) DEFAULT 'Futebol',
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

CREATE TABLE blocked_matches (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    match_id BIGINT NOT NULL, -- ID da BetsAPI
    reason VARCHAR(255),
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

CREATE TABLE blocked_odds (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    match_id BIGINT,
    market_name VARCHAR(100) NOT NULL,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- 13. REGIÕES (Gestão Geográfica de Cambistas)
CREATE TABLE regions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    manager_id BIGINT, -- Gerente responsável pela região
    status TINYINT DEFAULT 1,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- 14. NOTIFICAÇÕES DO SISTEMA
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT,
    user_id BIGINT, -- NULL = broadcast para todos
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    is_read TINYINT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 15. JOGO DESTAQUE (Featured Match)
CREATE TABLE featured_matches (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT NOT NULL,
    match_id BIGINT, -- ID da BetsAPI
    manual_event_id BIGINT, -- OU ID de evento manual
    position INT DEFAULT 0,
    status TINYINT DEFAULT 1,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (manual_event_id) REFERENCES manual_events(id)
);
