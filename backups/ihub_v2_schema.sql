-- Table: afer_tomorow_match_flashes
CREATE TABLE `afer_tomorow_match_flashes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dados` json DEFAULT NULL,
  `site_id` bigint unsigned NOT NULL DEFAULT '1',
  `sport_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `afer_tomorow_match_flashes_site_id_index` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: affiliates
CREATE TABLE `affiliates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '10.00',
  `visits` int NOT NULL DEFAULT '0',
  `registrations` int NOT NULL DEFAULT '0',
  `total_commissions` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliates_code_unique` (`code`),
  KEY `affiliates_user_id_foreign` (`user_id`),
  KEY `affiliates_site_id_foreign` (`site_id`),
  CONSTRAINT `affiliates_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affiliates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: apifootball_leagues
CREATE TABLE `apifootball_leagues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `season` int DEFAULT '2026',
  `sport` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'football',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apifootball_leagues_league_id_sport_unique` (`league_id`,`sport`),
  KEY `apifootball_leagues_site_id_active_index` (`site_id`,`active`),
  CONSTRAINT `apifootball_leagues_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: apostas
CREATE TABLE `apostas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `gerente_id` bigint unsigned DEFAULT NULL,
  `adm_id` bigint DEFAULT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Simples',
  `modalidade` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Futebol',
  `tipo_aposta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cupom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_apostado` decimal(15,2) NOT NULL DEFAULT '0.00',
  `retorno_possivel` decimal(15,2) NOT NULL DEFAULT '0.00',
  `retorno_cambista` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comicao` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cash_out_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Aberto',
  `codigo_bilhete` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_palpites` int NOT NULL DEFAULT '0',
  `vendedor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cliente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cotacao` decimal(15,2) NOT NULL DEFAULT '0.00',
  `invoice_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code` longtext COLLATE utf8mb4_unicode_ci,
  `qr_code_text` longtext COLLATE utf8mb4_unicode_ci,
  `andamento_palpites` int NOT NULL DEFAULT '0',
  `acertos_palpites` int NOT NULL DEFAULT '0',
  `erros_palpites` int NOT NULL DEFAULT '0',
  `devolvidos_palpites` int NOT NULL DEFAULT '0',
  `resultado_loto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rodada_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `apostas_codigo_bilhete_unique` (`codigo_bilhete`),
  KEY `apostas_site_id_index` (`site_id`),
  KEY `apostas_user_id_index` (`user_id`),
  KEY `apostas_gerente_id_index` (`gerente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: apostas_cassino
CREATE TABLE `apostas_cassino` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bet_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `game_id` int unsigned NOT NULL,
  `bet` decimal(12,2) NOT NULL,
  `win` decimal(12,2) NOT NULL,
  `bet_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `apostas_cassino_user_id_foreign` (`user_id`),
  KEY `apostas_cassino_site_id_user_id_index` (`site_id`,`user_id`),
  CONSTRAINT `apostas_cassino_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `apostas_cassino_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: audit_logs
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_site_id_foreign` (`site_id`),
  CONSTRAINT `audit_logs_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: balance_adjustments
CREATE TABLE `balance_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `performed_by` bigint unsigned NOT NULL,
  `type` enum('deposit','withdraw') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `balance_adjustments_user_id_foreign` (`user_id`),
  KEY `balance_adjustments_site_id_foreign` (`site_id`),
  KEY `balance_adjustments_performed_by_foreign` (`performed_by`),
  CONSTRAINT `balance_adjustments_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `master_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `balance_adjustments_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `balance_adjustments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: banner_assets
CREATE TABLE `banner_assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('background','icon','player','logo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'background',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: banner_templates
CREATE TABLE `banner_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('story','square','landscape') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'story',
  `layout_data` json NOT NULL,
  `background_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: banners
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'home_main',
  `display_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `order` int NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `order_index` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banners_site_id_foreign` (`site_id`),
  CONSTRAINT `banners_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: bet_items
CREATE TABLE `bet_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bet_id` bigint unsigned NOT NULL,
  `match_id` bigint NOT NULL,
  `home_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `away_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selection_odd` decimal(10,2) NOT NULL,
  `status` enum('pending','won','lost','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `league_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `market_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selection_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bet_items_bet_id_foreign` (`bet_id`),
  CONSTRAINT `bet_items_bet_id_foreign` FOREIGN KEY (`bet_id`) REFERENCES `bets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: bets
CREATE TABLE `bets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `manager_id` bigint unsigned DEFAULT NULL,
  `cambista_id` bigint unsigned DEFAULT NULL,
  `ticket_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `potential_payout` decimal(15,2) NOT NULL,
  `status` enum('open','won','lost','cancelled','cashed_out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `is_bonus_bet` tinyint(1) NOT NULL DEFAULT '0',
  `selections` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ticket_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `commission_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `manager_commission_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `prize_commission_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cashout_pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cash_out_amount` decimal(15,2) DEFAULT NULL,
  `can_cash_out` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bets_external_code_unique` (`ticket_code`),
  KEY `bets_site_id_foreign` (`site_id`),
  KEY `bets_user_id_foreign` (`user_id`),
  KEY `bets_ticket_signature_index` (`ticket_signature`),
  KEY `bets_cambista_id_index` (`cambista_id`),
  CONSTRAINT `bets_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`),
  CONSTRAINT `bets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: block_day_lotos
CREATE TABLE `block_day_lotos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: block_leagues
CREATE TABLE `block_leagues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `league` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `block_leagues_site_id_foreign` (`site_id`),
  CONSTRAINT `block_leagues_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: block_matchs
CREATE TABLE `block_matchs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `event_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `block_matchs_site_id_foreign` (`site_id`),
  CONSTRAINT `block_matchs_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: block_odd_matches
CREATE TABLE `block_odd_matches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `odd_id` bigint unsigned DEFAULT NULL,
  `odd_uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odd` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cotacao` decimal(10,2) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `site_id` bigint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `block_odd_matches_odd_id_index` (`odd_id`),
  KEY `block_odd_matches_site_id_index` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: bonus_user
CREATE TABLE `bonus_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `bonus_id` bigint unsigned NOT NULL,
  `initial_value` decimal(15,2) NOT NULL,
  `current_balance` decimal(15,2) NOT NULL,
  `target_rollover` decimal(15,2) NOT NULL,
  `current_rollover` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','completed','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonus_user_user_id_foreign` (`user_id`),
  KEY `bonus_user_bonus_id_foreign` (`bonus_id`),
  CONSTRAINT `bonus_user_bonus_id_foreign` FOREIGN KEY (`bonus_id`) REFERENCES `bonuses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bonus_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: bonuses
CREATE TABLE `bonuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(15,2) NOT NULL,
  `min_deposit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `rollover_multiplier` int NOT NULL DEFAULT '1',
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bonuses_code_unique` (`code`),
  KEY `bonuses_site_id_foreign` (`site_id`),
  CONSTRAINT `bonuses_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache_locks
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cashier_closeouts
CREATE TABLE `cashier_closeouts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `closed_by` bigint unsigned NOT NULL,
  `site_id` bigint unsigned NOT NULL DEFAULT '1',
  `turno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'integral',
  `total_entradas` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_saidas` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_comissoes` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_lancamentos` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_entradas_abertas` decimal(12,2) NOT NULL DEFAULT '0.00',
  `quantidade_apostas` int NOT NULL DEFAULT '0',
  `total_liquido` decimal(12,2) NOT NULL DEFAULT '0.00',
  `comissao_gerente` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_anterior` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_final` decimal(12,2) NOT NULL DEFAULT '0.00',
  `detalhes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashier_closeouts_user_id_site_id_index` (`user_id`,`site_id`),
  KEY `cashier_closeouts_site_id_created_at_index` (`site_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: config_mercados
CREATE TABLE `config_mercados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `porcentagem` decimal(10,2) DEFAULT '0.00',
  `status` int DEFAULT '1',
  `user_id` int DEFAULT NULL,
  `site_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ihub',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=440 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: config_odds
CREATE TABLE `config_odds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mercado_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mercado_full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `porcentagem` decimal(10,2) DEFAULT '0.00',
  `status` int DEFAULT '1',
  `user_id` int DEFAULT NULL,
  `site_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ihub',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `header` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: configuracaos
CREATE TABLE `configuracaos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ihub',
  `affiliate_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `affiliate_commission` decimal(5,2) NOT NULL DEFAULT '10.00',
  `valor_mini_aposta` decimal(10,2) DEFAULT '1.00',
  `valor_max_aposta` decimal(10,2) DEFAULT '1000.00',
  `menor_valor_loto` decimal(10,2) DEFAULT '1.00',
  `max_valor_loto` decimal(10,2) DEFAULT '1000.00',
  `premio_max` decimal(10,2) DEFAULT '10000.00',
  `cotacao_mini_bilhete` decimal(10,2) DEFAULT '1.01',
  `cotacao_max_bilhete` decimal(10,2) DEFAULT '1000.00',
  `bloquear_odd_abaixo` decimal(10,2) DEFAULT NULL,
  `travar_odd_acima` decimal(10,2) DEFAULT NULL,
  `quantidade_jogos_mini_bilhete` int DEFAULT '1',
  `quantidade_jogos_max_bilhete` int DEFAULT '20',
  `quantidade_times_visitantes_mesmo_camp` int DEFAULT NULL,
  `texto_rodape` text COLLATE utf8mb4_unicode_ci,
  `email_alerta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alerta_aposta_acima` decimal(10,2) DEFAULT NULL,
  `cambista_pode_cancelar` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempo_limite_camb_cancela_aposta` int DEFAULT '10',
  `aposta_ativa` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bloq_aposta_madrugada` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_limite_jogos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `op_futebol` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `op_ufcbox` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `op_quininha` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `op_seninha` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `op_basquete` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `op_tenis` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `op_volei` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sim',
  `cor_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#1b1b1b',
  `cor_secundaria` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#2ac2ba',
  `cor_fundo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#0a0e12',
  `cor_texto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#ffffff',
  `cor_botoes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_botoes_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_fundo_campeonato` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cash_out_ativo` tinyint(1) NOT NULL DEFAULT '0',
  `cash_out_taxa` decimal(5,2) NOT NULL DEFAULT '10.00',
  `op_e_sports` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Não',
  `futebol_ao_vivo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_live` int DEFAULT '85',
  `cotacao_live` decimal(10,2) DEFAULT '1.01',
  `comissao_premio` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_bonus_conversion` decimal(10,2) DEFAULT '500.00',
  `min_deposit` decimal(15,2) NOT NULL DEFAULT '20.00',
  `max_deposit` decimal(15,2) NOT NULL DEFAULT '10000.00',
  `min_withdrawal` decimal(15,2) NOT NULL DEFAULT '50.00',
  `max_withdrawal` decimal(15,2) NOT NULL DEFAULT '5000.00',
  `withdrawal_limit_day` int NOT NULL DEFAULT '3',
  `perc_sub_lv1` decimal(5,2) NOT NULL DEFAULT '10.00',
  `perc_sub_lv2` decimal(5,2) NOT NULL DEFAULT '5.00',
  `perc_sub_lv3` decimal(5,2) NOT NULL DEFAULT '2.00',
  `suitpay_client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suitpay_client_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active_deposit_gateway` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mercadopago',
  `active_withdrawal_gateway` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: custom_themes
CREATE TABLE `custom_themes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `colors` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: daily_cash_snapshots
CREATE TABLE `daily_cash_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `site_id` bigint unsigned NOT NULL DEFAULT '1',
  `snapshot_date` date NOT NULL,
  `entradas_dia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saidas_dia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `comissoes_dia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `lancamentos_dia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `apostas_dia` int NOT NULL DEFAULT '0',
  `lucro_dia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_fechamento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_cash_snapshots_user_id_snapshot_date_site_id_unique` (`user_id`,`snapshot_date`,`site_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: failed_jobs
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: featured_matches
CREATE TABLE `featured_matches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `is_manual` tinyint(1) NOT NULL DEFAULT '0',
  `manual_event_id` bigint unsigned DEFAULT NULL,
  `match_id` bigint DEFAULT NULL,
  `home_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `away_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sport` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'soccer',
  `league_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#ae8a36',
  `order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `featured_matches_site_id_foreign` (`site_id`),
  CONSTRAINT `featured_matches_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=158 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: futebol_lives
CREATE TABLE `futebol_lives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dados` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `futebol_lives_site_id_foreign` (`site_id`),
  CONSTRAINT `futebol_lives_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: gerenciador_crons
CREATE TABLE `gerenciador_crons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `action` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: global_themes
CREATE TABLE `global_themes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `colors` json DEFAULT NULL,
  `is_base` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `global_themes_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: job_batches
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jobs
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: lancamentos
CREATE TABLE `lancamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `site_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ihub',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: league_lives
CREATE TABLE `league_lives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int NOT NULL,
  `league` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_lives_site_id_foreign` (`site_id`),
  CONSTRAINT `league_lives_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: listleagues_mains
CREATE TABLE `listleagues_mains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sport` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `league_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `league` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: live_matches
CREATE TABLE `live_matches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dados` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `live_matches_site_id_foreign` (`site_id`),
  CONSTRAINT `live_matches_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: loto_results
CREATE TABLE `loto_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `concurso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_sorteio` date NOT NULL,
  `dezenas` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: main_leagues
CREATE TABLE `main_leagues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `league_id` bigint NOT NULL,
  `league` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Futebol',
  `order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `main_leagues_site_id_foreign` (`site_id`),
  CONSTRAINT `main_leagues_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: manual_categories
CREATE TABLE `manual_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: manual_events
CREATE TABLE `manual_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `home_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `away_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `league_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_flag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `away_flag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odd_home` decimal(10,2) NOT NULL DEFAULT '1.00',
  `odd_draw` decimal(10,2) NOT NULL DEFAULT '1.00',
  `odd_away` decimal(10,2) NOT NULL DEFAULT '1.00',
  `odd_btts_yes` decimal(8,2) DEFAULT NULL,
  `odd_btts_no` decimal(8,2) DEFAULT NULL,
  `odd_over_25` decimal(8,2) DEFAULT NULL,
  `odd_under_25` decimal(8,2) DEFAULT NULL,
  `has_extra_markets` tinyint(1) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL,
  `status` enum('open','finished','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `img_featured` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_badge` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_markets` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manual_events_category_id_foreign` (`category_id`),
  KEY `manual_events_site_id_foreign` (`site_id`),
  CONSTRAINT `manual_events_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `manual_categories` (`id`),
  CONSTRAINT `manual_events_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1044 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: manual_markets
CREATE TABLE `manual_markets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manual_markets_event_id_foreign` (`event_id`),
  CONSTRAINT `manual_markets_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `manual_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: manual_odds
CREATE TABLE `manual_odds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `market_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `is_winner` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manual_odds_market_id_foreign` (`market_id`),
  CONSTRAINT `manual_odds_market_id_foreign` FOREIGN KEY (`market_id`) REFERENCES `manual_markets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: marckets
CREATE TABLE `marckets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: master_users
CREATE TABLE `master_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint unsigned DEFAULT NULL,
  `referred_by_id` bigint unsigned DEFAULT NULL,
  `site_id` bigint unsigned DEFAULT NULL,
  `region_id` bigint unsigned DEFAULT NULL,
  `gerente_id` bigint unsigned DEFAULT NULL,
  `cambista_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `nivel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comissao1` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao2` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao3` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao4` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao5` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao6` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao7` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao8` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao9` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao10` decimal(5,2) NOT NULL DEFAULT '0.00',
  `online_comissao10` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao_online` decimal(5,2) NOT NULL DEFAULT '0.00',
  `comissao_gerente_online` decimal(5,2) NOT NULL DEFAULT '0.00',
  `online_comissao9` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao8` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao7` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao6` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao5` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao4` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao3` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao2` decimal(8,2) NOT NULL DEFAULT '0.00',
  `online_comissao1` decimal(8,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `credito` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_bonus` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rollover_meta` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rollover_atual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `promocao_ativa_id` int unsigned DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `comissao_gerente` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao_cambistas` decimal(8,2) NOT NULL DEFAULT '0.00',
  `commission_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
  `entradas` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saidas` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comissoes` decimal(15,2) NOT NULL DEFAULT '0.00',
  `lancamentos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantidade_aposta` int NOT NULL DEFAULT '0',
  `entrada_loto` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entrada_bolao` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entrada_casadinha` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entrada_simples` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance_bonus` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` tinyint NOT NULL DEFAULT '1',
  `situacao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `theme_preference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_create_coupons` tinyint(1) DEFAULT '0',
  `can_manage_bonuses` tinyint(1) NOT NULL DEFAULT '0',
  `pix_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_key_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `manager_commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `prize_paid_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `can_cancel_tickets` tinyint(1) NOT NULL DEFAULT '0',
  `comissao_loto` decimal(5,2) DEFAULT '0.00',
  `comissao_bolao` decimal(5,2) NOT NULL DEFAULT '0.00',
  `saldo_simples` decimal(15,2) DEFAULT '0.00',
  `saldo_casadinha` decimal(15,2) DEFAULT '0.00',
  `saldo_loto` decimal(15,2) DEFAULT '0.00',
  `saldo_bolao` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_gerente` decimal(15,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_users_cpf_unique` (`cpf`),
  UNIQUE KEY `master_users_site_id_username_unique` (`site_id`,`username`),
  KEY `master_users_region_id_foreign` (`region_id`),
  CONSTRAINT `master_users_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `master_users_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: match_inplays
CREATE TABLE `match_inplays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `match_id` int DEFAULT NULL,
  `matchs` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `match_inplays_site_id_foreign` (`site_id`),
  CONSTRAINT `match_inplays_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: matchs
CREATE TABLE `matchs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned DEFAULT NULL,
  `event_id` bigint NOT NULL,
  `our_event_id` bigint DEFAULT NULL,
  `sport_id` int NOT NULL DEFAULT '1',
  `sport_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Futebol',
  `league_id` bigint NOT NULL,
  `league_cc` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `league` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `home` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `away` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `home_true` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `away_true` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_id_home` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_id_away` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_status` int NOT NULL DEFAULT '0',
  `time` bigint DEFAULT NULL,
  `date` datetime NOT NULL,
  `confronto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visible` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sim',
  `order` int NOT NULL DEFAULT '0',
  `schedule` int NOT NULL DEFAULT '0',
  `live_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `halfTimeScoreHome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `halfTimeScoreAway` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fullTimeScoreHome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fullTimeScoreAway` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numberOfCornersHome` int DEFAULT NULL,
  `numberOfCornersAway` int DEFAULT NULL,
  `numberOfYellowCardsHome` int DEFAULT NULL,
  `numberOfYellowCardsAway` int DEFAULT NULL,
  `numberOfRedCardsHome` int DEFAULT NULL,
  `numberOfRedCardsAway` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `matchs_event_id_index` (`event_id`),
  KEY `matchs_league_id_index` (`league_id`),
  KEY `matchs_date_index` (`date`),
  KEY `matchs_site_id_index` (`site_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1335 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: migrations
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notifications
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_site_id_foreign` (`site_id`),
  CONSTRAINT `notifications_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: odd_marckets
CREATE TABLE `odd_marckets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mercado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `odd` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: odds
CREATE TABLE `odds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint NOT NULL,
  `market_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `mercado_full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selectionId` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int DEFAULT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `goals` double DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `odds_event_id_index` (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7517 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: palpite_bolao
CREATE TABLE `palpite_bolao` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aposta_id` bigint unsigned NOT NULL,
  `rodada_id` bigint unsigned NOT NULL,
  `match_id` bigint unsigned NOT NULL,
  `home` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `away` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mercado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Resultado Final',
  `palpite` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Aberto',
  `resultado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: palpite_lotos
CREATE TABLE `palpite_lotos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aposta_id` bigint unsigned NOT NULL,
  `concurso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dezena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Aberto',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: palpites
CREATE TABLE `palpites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aposta_id` bigint unsigned NOT NULL,
  `match_id` bigint unsigned DEFAULT NULL,
  `home_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `away_team` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `market_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selection_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selection_odd` decimal(10,2) NOT NULL DEFAULT '1.00',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Aberto',
  `score` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `palpites_aposta_id_index` (`aposta_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: password_reset_tokens
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pix_deposits
CREATE TABLE `pix_deposits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `external_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mp_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_base64` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pix_deposits_external_reference_unique` (`external_reference`),
  KEY `pix_deposits_site_id_foreign` (`site_id`),
  KEY `pix_deposits_user_id_foreign` (`user_id`),
  CONSTRAINT `pix_deposits_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pix_deposits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: playfiver_games
CREATE TABLE `playfiver_games` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `game_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `original` tinyint(1) NOT NULL DEFAULT '0',
  `is_popular` tinyint(1) NOT NULL DEFAULT '0',
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `playfiver_games_game_code_unique` (`game_code`),
  KEY `playfiver_games_site_id_status_index` (`site_id`,`status`),
  CONSTRAINT `playfiver_games_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: playfiver_providers
CREATE TABLE `playfiver_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallet_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `playfiver_providers_provider_id_unique` (`provider_id`),
  KEY `playfiver_providers_site_id_foreign` (`site_id`),
  CONSTRAINT `playfiver_providers_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pre_bets
CREATE TABLE `pre_bets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `selections` json NOT NULL,
  `total_stake` decimal(15,2) NOT NULL,
  `possible_return` decimal(15,2) NOT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidade` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Esporte',
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `concurso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pre_bets_code_unique` (`code`),
  KEY `pre_bets_site_id_index` (`site_id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: promocodes
CREATE TABLE `promocodes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `manager_id` int DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `min_deposit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rollover` int NOT NULL DEFAULT '1',
  `min_odd` decimal(5,2) NOT NULL DEFAULT '1.50',
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `promocodes_code_unique` (`code`),
  KEY `promocodes_site_id_foreign` (`site_id`),
  CONSTRAINT `promocodes_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: promocoes
CREATE TABLE `promocoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `porcentagem` decimal(8,2) NOT NULL DEFAULT '0.00',
  `valor_maximo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rollover_multiplicador` decimal(8,2) NOT NULL DEFAULT '1.00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promocoes_site_id_foreign` (`site_id`),
  CONSTRAINT `promocoes_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: quina_taxas
CREATE TABLE `quina_taxas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dezena` int NOT NULL,
  `taxa` decimal(10,2) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `site_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ihub',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: regions
CREATE TABLE `regions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `regions_site_id_foreign` (`site_id`),
  CONSTRAINT `regions_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: result_manuals
CREATE TABLE `result_manuals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `score_ful_home` int NOT NULL,
  `score_ful_away` int NOT NULL,
  `score_half_home` int NOT NULL,
  `score_half_away` int NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `result_manuals_site_id_event_id_index` (`site_id`,`event_id`),
  CONSTRAINT `result_manuals_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rodadas
CREATE TABLE `rodadas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL DEFAULT '1',
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Aberta',
  `premio_max` decimal(15,2) NOT NULL DEFAULT '0.00',
  `premio_primeiro` decimal(15,2) NOT NULL DEFAULT '0.00',
  `premio_segundo` decimal(15,2) NOT NULL DEFAULT '0.00',
  `premio_terceiro` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantidade` int NOT NULL DEFAULT '0',
  `arrecadado` decimal(15,2) NOT NULL DEFAULT '0.00',
  `data_fechamento` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: saques
CREATE TABLE `saques` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `site_id` bigint unsigned NOT NULL DEFAULT '1',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Em processamento',
  `valor` decimal(10,2) NOT NULL,
  `pix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_pix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'random',
  `admin_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saques_user_id_foreign` (`user_id`),
  KEY `saques_site_id_status_index` (`site_id`,`status`),
  CONSTRAINT `saques_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sena_taxas
CREATE TABLE `sena_taxas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dezena` int NOT NULL,
  `taxa` decimal(10,2) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `site_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ihub',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: servicos
CREATE TABLE `servicos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `action` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sessions
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: site_pages
CREATE TABLE `site_pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `page_type` enum('regulamento','sobre_nos','compartilhamentos','termos') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site_pages_site_id_foreign` (`site_id`),
  CONSTRAINT `site_pages_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: site_settings
CREATE TABLE `site_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `min_bet_amount` decimal(10,2) NOT NULL DEFAULT '2.00',
  `max_bet_amount` decimal(10,2) NOT NULL DEFAULT '1000.00',
  `max_payout` decimal(15,2) NOT NULL DEFAULT '10000.00',
  `min_withdrawal` decimal(10,2) NOT NULL DEFAULT '50.00',
  `cashout_tax` decimal(5,2) NOT NULL DEFAULT '10.00',
  `cashout_delay_seconds` int NOT NULL DEFAULT '5',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `aposta_ativa` tinyint(1) NOT NULL DEFAULT '1',
  `bloq_aposta_madrugada` tinyint(1) NOT NULL DEFAULT '1',
  `bloquear_odd_abaixo` decimal(15,2) NOT NULL DEFAULT '1.00',
  `travar_odd_acima` decimal(15,2) NOT NULL DEFAULT '1000.00',
  `data_limite_jogos` date NOT NULL DEFAULT '2050-12-31',
  `hours_limit_date` time NOT NULL DEFAULT '23:59:59',
  `limite_apostas_iguais` int NOT NULL DEFAULT '0',
  `alerta_aposta_acima` decimal(15,2) NOT NULL DEFAULT '500.00',
  `cotacao_mini_bilhete_mult` decimal(15,2) NOT NULL DEFAULT '1.40',
  `api_provider` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'api-football',
  `live_valor_mini_aposta` decimal(15,2) NOT NULL DEFAULT '2.00',
  `live_valor_max_aposta` decimal(15,2) NOT NULL DEFAULT '500.00',
  `live_premio_max` decimal(15,2) NOT NULL DEFAULT '10000.00',
  `live_cotacao_mini_bilhete` decimal(15,2) NOT NULL DEFAULT '2.00',
  `quantidade_jogos_mini_bilhete` int NOT NULL DEFAULT '1',
  `quantidade_jogos_max_bilhete` int NOT NULL DEFAULT '25',
  `live_quantidade_jogos_mini_bilhete` int NOT NULL DEFAULT '1',
  `live_quantidade_jogos_max_bilhete` int NOT NULL DEFAULT '15',
  `site_language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'pt_BR',
  `language_selector_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `alert_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sellers_can_cancel` tinyint(1) NOT NULL DEFAULT '1',
  `cancellation_time_limit` int NOT NULL DEFAULT '5',
  `manager_can_cancel` tinyint(1) NOT NULL DEFAULT '1',
  `manager_can_create_sellers` tinyint(1) NOT NULL DEFAULT '1',
  `manager_can_remove_sellers` tinyint(1) NOT NULL DEFAULT '1',
  `manager_can_edit_sellers` tinyint(1) NOT NULL DEFAULT '1',
  `validate_pin_once` tinyint(1) NOT NULL DEFAULT '0',
  `reduce_increase_odds_pin` tinyint(1) NOT NULL DEFAULT '0',
  `carousel_banners` tinyint(1) NOT NULL DEFAULT '1',
  `whatsapp_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_support_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_text` text COLLATE utf8mb4_unicode_ci,
  `theme_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'verde_claro',
  `custom_colors_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `sidebar_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#000000',
  `game_container_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#ffffff',
  `logo_container_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#ffffff',
  `button_odds_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#007bff',
  `button_home_draw_away_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#28a745',
  `background_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#f4f6f9',
  `lines_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#dee2e6',
  `allow_mixed_bets` tinyint(1) NOT NULL DEFAULT '1',
  `google_analytics_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `google_analytics_script` text COLLATE utf8mb4_unicode_ci,
  `meta_pixel_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `meta_pixel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_selected_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#ffc107',
  `button_selected_border_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#ffc107',
  `hover_menu_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#343a40',
  `main_menu_button_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#000000',
  `save_button_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '#28a745',
  `advanced_share` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `site_settings_site_id_foreign` (`site_id`),
  CONSTRAINT `site_settings_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sites
CREATE TABLE `sites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complete_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','suspended','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `billing_status` enum('paid','pending','overdue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `layout_theme` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `primary_color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1c3464',
  `secondary_color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#2a4b8d',
  `theme_color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'verde-claro',
  `logo_path` text COLLATE utf8mb4_unicode_ci,
  `favicon_path` text COLLATE utf8mb4_unicode_ci,
  `seniha_enabled` tinyint NOT NULL DEFAULT '1',
  `queniha_enabled` tinyint NOT NULL DEFAULT '1',
  `loto_enabled` tinyint NOT NULL DEFAULT '1',
  `bonus_enabled` tinyint NOT NULL DEFAULT '0',
  `cashout_enabled` tinyint NOT NULL DEFAULT '1',
  `due_value` decimal(15,2) NOT NULL DEFAULT '500.00',
  `billing_day` int NOT NULL DEFAULT '10',
  `next_due_date` date DEFAULT NULL,
  `regulation` longtext COLLATE utf8mb4_unicode_ci,
  `whatsapp_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mercado_pago',
  `pix_client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_client_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_withdrawal` decimal(15,2) NOT NULL DEFAULT '20.00',
  `max_withdrawal` decimal(15,2) NOT NULL DEFAULT '1000.00',
  `daily_withdrawal_limit` decimal(15,2) NOT NULL DEFAULT '5000.00',
  `texto_rodape_bilhete` text COLLATE utf8mb4_unicode_ci,
  `social_instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_twitter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carrosel_ativado` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `facebook_pixel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_access_token` text COLLATE utf8mb4_unicode_ci,
  `active_custom_colors` tinyint(1) NOT NULL DEFAULT '0',
  `custom_themes` json DEFAULT NULL,
  `custom_colors` json DEFAULT NULL,
  `sidebar_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sidebar_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `game_container_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_header_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_header_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_container_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_bar_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_bar_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odds_button_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bet_button_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odds_plus_button_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odd_button_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odd_button_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odd_button_hover_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odd_button_hover_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bet_main_buttons_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `border_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_selected_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_selected_border_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_hover_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_hover_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_button_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_button_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cupom_valor_btn_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cupom_header_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cupom_apostar_btn_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidade_ativa_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `btn_entrar_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `btn_entrar_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `btn_cadastrar_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `btn_cadastrar_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cupom_apostar_btn_hover_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cupom_valor_btn_hover_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `odds_plus_button_hover_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tab_active_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tab_active_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_item_active_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_item_active_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `btn_primary_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `btn_login_border_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destaque_header_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destaque_header_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destaque_btn_bg_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destaque_btn_text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ticket_model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'modelo_1',
  `bluetooth_print_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `about_us` text COLLATE utf8mb4_unicode_ci,
  `share_links` text COLLATE utf8mb4_unicode_ci,
  `featured_games_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marketing_image_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marketing_image_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prem_max_pre` decimal(12,2) DEFAULT '50000.00',
  `val_min_pre` decimal(12,2) DEFAULT '1.00',
  `val_max_pre` decimal(12,2) DEFAULT '1000.00',
  `cot_min_pre` decimal(8,2) DEFAULT '1.40',
  `cot_max_pre` decimal(8,2) DEFAULT '1000.00',
  `qtd_min_pre` int DEFAULT '1',
  `qtd_max_pre` int DEFAULT '12',
  `odd_max_pre` decimal(8,2) DEFAULT '100.00',
  `block_odds_below` decimal(8,2) DEFAULT '1.00',
  `min_valid_pin` int DEFAULT '500',
  `min_before_game` int DEFAULT '0',
  `qtd_min_live` int DEFAULT '1',
  `val_min_live` decimal(12,2) DEFAULT '2.00',
  `val_max_live` decimal(12,2) DEFAULT '500.00',
  `cot_min_live` decimal(8,2) DEFAULT '2.00',
  `cot_max_live` decimal(8,2) DEFAULT '1000.00',
  `odd_max_live` decimal(8,2) DEFAULT '100.00',
  `cot_min_comm` decimal(8,2) DEFAULT '2.00',
  `prem_max_live` decimal(12,2) DEFAULT '10000.00',
  `accept_bet_until` int DEFAULT '90',
  `alt_cot_live` decimal(8,2) DEFAULT '0.00',
  `prem_max_equal` decimal(12,2) DEFAULT '0.00',
  `active_bets` tinyint(1) DEFAULT '1',
  `merge_pre_live` tinyint(1) DEFAULT '1',
  `site_lang` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'pt_BR',
  `lang_selector` tinyint(1) DEFAULT '0',
  `cancel_time_minutes` int DEFAULT '10',
  `ga_code` text COLLATE utf8mb4_unicode_ci,
  `pixel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ga_enabled` tinyint(1) DEFAULT '0',
  `pixel_enabled` tinyint(1) DEFAULT '0',
  `menor_valor_loto` decimal(12,2) DEFAULT '1.00',
  `max_valor_loto` decimal(12,2) DEFAULT '1000.00',
  `time_live` int DEFAULT '80',
  `cotacao_live` decimal(8,2) DEFAULT '1.01',
  `futebol_ao_vivo` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `comissao_premio` decimal(8,2) DEFAULT '0.00',
  `email_alerta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alerta_aposta_acima` decimal(12,2) DEFAULT '100.00',
  `travar_odd_acima` decimal(8,2) DEFAULT '500.00',
  `cambista_pode_cancelar` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `tempo_limite_camb_cancela_aposta` int DEFAULT '30',
  `bloq_aposta_madrugada` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Não',
  `data_limite_jogos` date DEFAULT NULL,
  `op_futebol` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `op_quininha` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `op_seninha` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `op_ufcbox` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `op_basquete` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `op_tenis` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `op_volei` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT 'Sim',
  `active_affiliates` tinyint NOT NULL DEFAULT '1',
  `active_payments` tinyint NOT NULL DEFAULT '1',
  `active_mercado_pago` tinyint NOT NULL DEFAULT '1',
  `active_loto` tinyint NOT NULL DEFAULT '1',
  `active_marketing` tinyint(1) DEFAULT '1',
  `active_bonus` tinyint(1) NOT NULL DEFAULT '1',
  `active_configuracoes` tinyint(1) NOT NULL DEFAULT '1',
  `active_relatorios` tinyint(1) NOT NULL DEFAULT '1',
  `active_riscos` tinyint(1) NOT NULL DEFAULT '1',
  `active_online_users` tinyint(1) NOT NULL DEFAULT '1',
  `active_lancamentos` tinyint(1) NOT NULL DEFAULT '1',
  `active_extrato` tinyint(1) NOT NULL DEFAULT '1',
  `active_banner_generator` tinyint(1) NOT NULL DEFAULT '1',
  `active_gateway_deposito` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sites_uuid_unique` (`uuid`),
  UNIQUE KEY `sites_domain_unique` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: teams
CREATE TABLE `teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_site_id_foreign` (`site_id`),
  CONSTRAINT `teams_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=319 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: traducoes
CREATE TABLE `traducoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto_traduzido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `traducoes_site_id_tipo_index` (`site_id`,`tipo`),
  KEY `traducoes_texto_original_index` (`texto_original`),
  CONSTRAINT `traducoes_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: transactions
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `gateway_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','completed','cancelled','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_site_id_foreign` (`site_id`),
  KEY `transactions_user_id_foreign` (`user_id`),
  CONSTRAINT `transactions_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint unsigned DEFAULT NULL,
  `referred_by_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `nivel` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `situacao` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ativo',
  `site_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ihub',
  `adm_id` bigint unsigned DEFAULT NULL,
  `gerente_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `contato` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_key_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comissao1` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao2` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao3` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao4` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao5` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao6` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao7` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao8` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao9` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao10` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao_gerente` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao_cambistas` decimal(8,2) NOT NULL DEFAULT '0.00',
  `comissao_loto` decimal(8,2) NOT NULL DEFAULT '0.00',
  `commission_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
  `saldo_casadinha` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_loto` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_simples` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_gerente` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entradas` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entrada_loto` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entrada_casadinha` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entrada_simples` decimal(15,2) NOT NULL DEFAULT '0.00',
  `entradas_abertas` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saidas` decimal(15,2) NOT NULL DEFAULT '0.00',
  `comissoes` decimal(15,2) NOT NULL DEFAULT '0.00',
  `lancamentos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantidade_aposta` int NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `quantidade` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_site_id_index` (`site_id`),
  KEY `users_nivel_index` (`nivel`),
  KEY `users_gerente_id_index` (`gerente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: verify_users
CREATE TABLE `verify_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `verify_users_user_id_index` (`user_id`),
  CONSTRAINT `verify_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: wallets
CREATE TABLE `wallets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `balance_real` decimal(15,2) NOT NULL DEFAULT '0.00',
  `balance_bonus` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_user_id_unique` (`user_id`),
  KEY `wallets_site_id_foreign` (`site_id`),
  CONSTRAINT `wallets_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: withdrawal_requests
CREATE TABLE `withdrawal_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `amount` decimal(15,2) NOT NULL,
  `pix_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pix_key_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawal_requests_site_id_foreign` (`site_id`),
  KEY `withdrawal_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `withdrawal_requests_site_id_foreign` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `withdrawal_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `master_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

