-- Stored Procedures ESG-BALANCE

USE esg_balance;

DELIMITER $$

CREATE PROCEDURE sp_login(
    IN p_username VARCHAR(50)
)
BEGIN
    SELECT username, password_hash, ruolo
    FROM utenti
    WHERE username = p_username;
END$$

CREATE PROCEDURE sp_registra_utente(
    IN p_username       VARCHAR(50),
    IN p_password_hash  VARCHAR(255),
    IN p_codice_fiscale CHAR(16),
    IN p_data_nascita   DATE,
    IN p_luogo_nascita  VARCHAR(100),
    IN p_ruolo          VARCHAR(20),
    IN p_email          VARCHAR(150),
    IN p_curriculum_pdf VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO utenti (username, password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo)
    VALUES (p_username, p_password_hash, p_codice_fiscale, p_data_nascita, p_luogo_nascita, p_ruolo);

    INSERT INTO email_utente (username, email)
    VALUES (p_username, p_email);

    IF p_ruolo = 'revisore' THEN
        INSERT INTO revisori (username) VALUES (p_username);
    ELSEIF p_ruolo = 'responsabile' THEN
        INSERT INTO responsabili (username, curriculum_pdf) VALUES (p_username, p_curriculum_pdf);
    END IF;

    COMMIT;
END$$

CREATE PROCEDURE sp_aggiungi_email(
    IN p_username   VARCHAR(50),
    IN p_email      VARCHAR(150)
)
BEGIN
    INSERT INTO email_utente (username, email) VALUES (p_username, p_email);
END$$

-- voce contabile (solo admin)
CREATE PROCEDURE sp_crea_voce_contabile(
    IN p_nome VARCHAR(150),
    IN p_descrizione TEXT
)
BEGIN
    INSERT INTO voci_contabili (nome, descrizione) VALUES (p_nome, p_descrizione);
END$$

-- inserisce l'indicatore e, se ambientale/sociale, anche nella sotto-tabella
CREATE PROCEDURE sp_inserisci_indicatore_esg(
    IN p_nome VARCHAR(150),
    IN p_immagine VARCHAR(255),
    IN p_rilevanza DECIMAL(3,1),
    IN p_tipo VARCHAR(20),
    IN p_codice_normativa   VARCHAR(100),
    IN p_ambito_sociale     VARCHAR(150),
    IN p_frequenza_rilev    VARCHAR(100)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo)
    VALUES (p_nome, p_immagine, p_rilevanza, IF(p_tipo = '', NULL, p_tipo));

    IF p_tipo = 'ambientale' THEN
        INSERT INTO indicatori_ambientali (nome, codice_normativa)
        VALUES (p_nome, p_codice_normativa);
    ELSEIF p_tipo = 'sociale' THEN
        INSERT INTO indicatori_sociali (nome, ambito_sociale, frequenza_rilevazione)
        VALUES (p_nome, p_ambito_sociale, p_frequenza_rilev);
    END IF;

    COMMIT;
END$$

CREATE PROCEDURE sp_registra_azienda(
    IN p_nome               VARCHAR(150),
    IN p_ragione_sociale    VARCHAR(200),
    IN p_partita_iva        VARCHAR(11),
    IN p_settore            VARCHAR(100),
    IN p_num_dipendenti     INT,
    IN p_logo               VARCHAR(255),
    IN p_username_resp      VARCHAR(50)
)
BEGIN
    INSERT INTO aziende (nome, ragione_sociale, partita_iva, settore, num_dipendenti, logo, username_responsabile)
    VALUES (p_nome, p_ragione_sociale, p_partita_iva, p_settore, p_num_dipendenti, p_logo, p_username_resp);
END$$

-- creazione bilancio, stato iniziale = bozza
CREATE PROCEDURE sp_crea_bilancio(
    IN p_id_azienda INT,
    IN p_anno       YEAR
)
BEGIN
    INSERT INTO bilanci (id_azienda, anno, data_creazione, stato)
    VALUES (p_id_azienda, p_anno, CURDATE(), 'bozza');

    SELECT LAST_INSERT_ID() AS id_bilancio;
END$$

-- upsert valore voce contabile
CREATE PROCEDURE sp_inserisci_valore_bilancio(
    IN p_id_bilancio INT,
    IN p_nome_voce VARCHAR(150),
    IN p_valore DECIMAL(15,2)
)
BEGIN
    INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore)
    VALUES (p_id_bilancio, p_nome_voce, p_valore)
    ON DUPLICATE KEY UPDATE valore = p_valore;
END$$

CREATE PROCEDURE sp_collega_indicatore_voce(
    IN p_id_bilancio        INT,
    IN p_nome_voce          VARCHAR(150),
    IN p_nome_indicatore    VARCHAR(150),
    IN p_valore             DECIMAL(15,2),
    IN p_fonte              VARCHAR(255),
    IN p_data_rilevazione   DATE
)
BEGIN
    INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione)
    VALUES (p_id_bilancio, p_nome_voce, p_nome_indicatore, p_valore, p_fonte, p_data_rilevazione)
    ON DUPLICATE KEY UPDATE
        valore_indicatore = p_valore,
        fonte = p_fonte,
        data_rilevazione = p_data_rilevazione;
END$$

CREATE PROCEDURE sp_associa_revisore_bilancio(
    IN p_username_revisore  VARCHAR(50),
    IN p_id_bilancio        INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    INSERT INTO revisioni (username_revisore, id_bilancio)
    VALUES (p_username_revisore, p_id_bilancio);

    UPDATE revisori
    SET nr_revisioni = nr_revisioni + 1
    WHERE username = p_username_revisore;

    COMMIT;
END$$

-- upsert competenza
CREATE PROCEDURE sp_inserisci_competenza(
    IN p_username       VARCHAR(50),
    IN p_nome_comp      VARCHAR(100),
    IN p_livello        TINYINT
)
BEGIN
    INSERT INTO competenze_revisore (username, nome_competenza, livello)
    VALUES (p_username, p_nome_comp, p_livello)
    ON DUPLICATE KEY UPDATE livello = p_livello;
END$$

CREATE PROCEDURE sp_inserisci_nota(
    IN p_username_rev   VARCHAR(50),
    IN p_id_bilancio    INT,
    IN p_nome_voce      VARCHAR(150),
    IN p_testo          TEXT
)
BEGIN
    INSERT INTO note_revisione (username_revisore, id_bilancio, nome_voce, data_nota, testo)
    VALUES (p_username_rev, p_id_bilancio, p_nome_voce, CURDATE(), p_testo);
END$$

-- giudizio complessivo, il trigger T2 aggiorna lo stato
CREATE PROCEDURE sp_inserisci_giudizio(
    IN p_username_rev   VARCHAR(50),
    IN p_id_bilancio    INT,
    IN p_esito          VARCHAR(30),
    IN p_rilievi        TEXT
)
BEGIN
    INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi)
    VALUES (p_username_rev, p_id_bilancio, p_esito, CURDATE(), p_rilievi);
END$$

-- ricalcolo indice affidabilità revisore (chiamata dal trigger T2)
CREATE PROCEDURE sp_aggiorna_indice_affidabilita(
    IN p_username VARCHAR(50)
)
BEGIN
    DECLARE v_totale   INT DEFAULT 0;
    DECLARE v_positivi INT DEFAULT 0;
    DECLARE v_indice   DECIMAL(3,2) DEFAULT 0.00;

    SELECT COUNT(*) INTO v_totale
    FROM giudizi
    WHERE username_revisore = p_username;

    SELECT COUNT(*) INTO v_positivi
    FROM giudizi
    WHERE username_revisore = p_username
      AND esito = 'approvazione';

    IF v_totale > 0 THEN
        SET v_indice = ROUND(v_positivi / v_totale, 2);
    END IF;

    UPDATE revisori
    SET indice_affidabilita = v_indice
    WHERE username = p_username;
END$$

DELIMITER ;
