-- =======================
-- SCALABLE DATABASE SCHEMA
-- =======================

-- 1. GAMES TABLE (replaces {gameID}_game tables)
CREATE TABLE games (
    id INT PRIMARY KEY,                          -- Game ID (10000-99999)
    status ENUM('setup', 'playing', 'finished') DEFAULT 'setup',
    phase INT DEFAULT 0,                         -- Current game phase
    night_number INT DEFAULT 1,                  -- Current night
    
    -- Game settings
    charaktere_aufdecken TINYINT DEFAULT 0,
    buergermeister_weitergeben TINYINT DEFAULT 0,
    seher_sieht_identitaet TINYINT DEFAULT 1,
    zufaellige_auswahl TINYINT DEFAULT 0,
    zufaellige_auswahl_bonus INT DEFAULT 0,
    werwolfe_einstimmig TINYINT DEFAULT 1,
    werwolf_opfer INT DEFAULT -1,
    
    -- Character counts
    werwolf_count INT DEFAULT 0,
    hexe_count INT DEFAULT 0,
    seher_count INT DEFAULT 0,
    jaeger_count INT DEFAULT 0,
    amor_count INT DEFAULT 0,
    beschuetzer_count INT DEFAULT 0,
    par_erm_count INT DEFAULT 0,
    lykantrophen_count INT DEFAULT 0,
    spione_count INT DEFAULT 0,
    idioten_count INT DEFAULT 0,
    pazifisten_count INT DEFAULT 0,
    alten_count INT DEFAULT 0,
    urwolf_count INT DEFAULT 0,
    
    -- Timers
    werwolf_timer1 INT DEFAULT 60,
    werwolf_zusatz1 INT DEFAULT 4,
    werwolf_timer2 INT DEFAULT 50,
    werwolf_zusatz2 INT DEFAULT 3,
    dorf_timer INT DEFAULT 550,
    dorf_zusatz INT DEFAULT 10,
    dorf_stichwahl_timer INT DEFAULT 200,
    dorf_stichwahl_zusatz INT DEFAULT 5,
    inaktiv_zeit INT DEFAULT 40,
    inaktiv_zeit_zusatz INT DEFAULT 0,
    
    -- Game data
    tages_text TEXT,
    waiting_for_others_time BIGINT,
    letzter_aufruf BIGINT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_phase (phase),
    INDEX idx_letzter_aufruf (letzter_aufruf)
);

-- 2. PLAYERS TABLE (replaces {gameID}_spieler tables)
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Unique player ID
    game_id INT NOT NULL,                       -- References games.id
    player_number INT NOT NULL,                 -- Player number within game (0,1,2...)
    name VARCHAR(150) NOT NULL,
    
    -- Player status
    is_alive TINYINT DEFAULT 1,
    is_game_master TINYINT DEFAULT 0,
    is_ready TINYINT DEFAULT 0,
    needs_reload TINYINT DEFAULT 0,
    died_this_night TINYINT DEFAULT 0,
    
    -- Game role and voting
    night_identity INT DEFAULT 0,               -- Character role constant
    vote_target INT DEFAULT -1,                 -- Player number being voted for
    accused_by INT DEFAULT -1,                  -- Player number who accused
    
    -- Special roles data
    is_mayor TINYINT DEFAULT 0,
    verliebt_mit INT DEFAULT -1,                -- In love with player number
    
    -- Witch specific
    hexe_heiltraenke INT DEFAULT 0,
    hexe_todestraenke INT DEFAULT 0,
    hexe_opfer INT DEFAULT -1,
    hexe_heilt TINYINT DEFAULT 0,
    
    -- Other role specific
    beschuetzer_letzte_runde_beschuetzt INT DEFAULT -1,
    par_erm_eingesetzt TINYINT DEFAULT 0,
    jaeger_darf_schiessen TINYINT DEFAULT 0,
    buergermeister_darf_weitergeben TINYINT DEFAULT 0,
    urwolf_anzahl_faehigkeiten INT DEFAULT 0,
    
    -- Timing
    countdown_bis BIGINT DEFAULT 0,
    countdown_ab BIGINT DEFAULT 0,
    
    -- Session
    verification_number INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    UNIQUE KEY unique_player_per_game (game_id, player_number),
    INDEX idx_game_alive (game_id, is_alive),
    INDEX idx_game_verification (game_id, verification_number),
    INDEX idx_vote_target (game_id, vote_target),
    INDEX idx_night_identity (game_id, night_identity)
);

-- 3. GAME LOGS TABLE (separate large text data)
CREATE TABLE game_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    log_type ENUM('game', 'player') DEFAULT 'game',
    player_id INT NULL,                         -- Only for player logs
    content LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    INDEX idx_game_type (game_id, log_type),
    INDEX idx_player_logs (player_id)
);

-- 4. PLAYER MESSAGES TABLE (popup messages)
CREATE TABLE player_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    message_text TEXT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    INDEX idx_player_unread (player_id, is_read)
);

-- 5. GAME_CACHE TABLE (for list caching)
CREATE TABLE game_cache (
    game_id INT NOT NULL,
    cache_key VARCHAR(50) NOT NULL,             -- 'list_alive', 'list_dead'
    cache_data LONGTEXT,
    updated_at BIGINT,
    
    PRIMARY KEY (game_id, cache_key),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    INDEX idx_updated_at (updated_at)
);

-- =======================
-- MIGRATION HELPER VIEWS
-- =======================

-- Create views to help with migration (optional)
CREATE VIEW v_game_players AS
SELECT 
    g.id as game_id,
    p.player_number,
    p.name,
    p.is_alive as lebt,
    p.night_identity as nachtIdentitaet,
    p.vote_target as wahlAuf,
    p.accused_by as angeklagtVon,
    p.is_mayor as buergermeister,
    p.verification_number as verifizierungsnr
FROM games g
JOIN players p ON g.id = p.game_id;