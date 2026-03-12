-- ESG-BALANCE: Schema del database
-- Corso di Basi di Dati, A.A. 2025/2026
-- CdS Informatica per il Management - Universita' di Bologna

DROP DATABASE IF EXISTS esg_balance;
CREATE DATABASE esg_balance
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE esg_balance;

-- 1. UTENTI
-- Tabella padre della gerarchia totale/esclusiva.
-- Ogni utente ha esattamente un ruolo.
CREATE TABLE utenti (
    username        VARCHAR(50)     NOT NULL,
    password_hash   VARCHAR(255)    NOT NULL,
    codice_fiscale  CHAR(16)        NOT NULL,
    data_nascita    DATE            NOT NULL,
    luogo_nascita   VARCHAR(100)    NOT NULL,
    ruolo           ENUM('amministratore','revisore','responsabile') NOT NULL,
    PRIMARY KEY (username),
    UNIQUE KEY (codice_fiscale)
) ENGINE=InnoDB;

-- 2. EMAIL_UTENTE
-- Un utente puo' avere uno o piu' recapiti email.
CREATE TABLE email_utente (
    username    VARCHAR(50)     NOT NULL,
    email       VARCHAR(150)    NOT NULL,
    PRIMARY KEY (username, email),
    FOREIGN KEY (username) REFERENCES utenti(username) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. REVISORI
-- Sotto-entita' di utenti (ruolo = 'revisore').
-- nr_revisioni e indice_affidabilita sono campi aggiuntivi.
CREATE TABLE revisori (
    username            VARCHAR(50)     NOT NULL,
    nr_revisioni        INT             DEFAULT 0,
    indice_affidabilita DECIMAL(3,2)    DEFAULT 0.00,
    PRIMARY KEY (username),
    FOREIGN KEY (username) REFERENCES utenti(username) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. RESPONSABILI
-- Sotto-entita' di utenti (ruolo = 'responsabile').
-- Campo aggiuntivo: curriculum vitae in formato PDF.
CREATE TABLE responsabili (
    username        VARCHAR(50)     NOT NULL,
    curriculum_pdf  VARCHAR(255)    DEFAULT NULL,
    PRIMARY KEY (username),
    FOREIGN KEY (username) REFERENCES utenti(username) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. COMPETENZE_REVISORE
-- Lista competenze di ogni revisore con livello (0-5).
CREATE TABLE competenze_revisore (
    username            VARCHAR(50)     NOT NULL,
    nome_competenza     VARCHAR(100)    NOT NULL,
    livello             TINYINT         NOT NULL,
    PRIMARY KEY (username, nome_competenza),
    FOREIGN KEY (username) REFERENCES revisori(username) ON DELETE CASCADE,
    CONSTRAINT chk_livello CHECK (livello BETWEEN 0 AND 5)
) ENGINE=InnoDB;

-- 6. AZIENDE
-- Ogni azienda e' associata ad un solo responsabile aziendale.
-- nr_bilanci e' una ridondanza concettuale (mantenuta tramite trigger).
-- ragione_sociale e' univoca.
CREATE TABLE aziende (
    id                      INT             NOT NULL AUTO_INCREMENT,
    nome                    VARCHAR(150)    NOT NULL,
    ragione_sociale         VARCHAR(200)    NOT NULL,
    partita_iva             VARCHAR(11)     NOT NULL,
    settore                 VARCHAR(100)    DEFAULT NULL,
    num_dipendenti          INT             DEFAULT NULL,
    logo                    VARCHAR(255)    DEFAULT NULL,
    nr_bilanci              INT             DEFAULT 0,
    username_responsabile   VARCHAR(50)     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (ragione_sociale),
    UNIQUE KEY (partita_iva),
    FOREIGN KEY (username_responsabile) REFERENCES responsabili(username) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. VOCI_CONTABILI
-- Template condiviso: ogni voce ha un nome univoco e una descrizione.
-- Popolata solo dagli amministratori.
CREATE TABLE voci_contabili (
    nome        VARCHAR(150)    NOT NULL,
    descrizione TEXT            DEFAULT NULL,
    PRIMARY KEY (nome)
) ENGINE=InnoDB;

-- 8. BILANCI
-- Bilancio di esercizio di un'azienda.
-- Stato: bozza -> in_revisione -> approvato | respinto
CREATE TABLE bilanci (
    id              INT     NOT NULL AUTO_INCREMENT,
    id_azienda      INT     NOT NULL,
    data_creazione  DATE    NOT NULL,
    stato           ENUM('bozza','in_revisione','approvato','respinto') DEFAULT 'bozza',
    PRIMARY KEY (id),
    FOREIGN KEY (id_azienda) REFERENCES aziende(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. VALORI_BILANCIO
-- Associa un valore numerico ad ogni voce contabile di un bilancio.
CREATE TABLE valori_bilancio (
    id_bilancio INT             NOT NULL,
    nome_voce   VARCHAR(150)    NOT NULL,
    valore      DECIMAL(15,2)   NOT NULL,
    PRIMARY KEY (id_bilancio, nome_voce),
    FOREIGN KEY (id_bilancio) REFERENCES bilanci(id) ON DELETE CASCADE,
    FOREIGN KEY (nome_voce)   REFERENCES voci_contabili(nome) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 10. INDICATORI_ESG
-- Tabella padre della gerarchia parziale/esclusiva.
-- tipo NULL = indicatore generico (non ambientale ne' sociale).
CREATE TABLE indicatori_esg (
    nome        VARCHAR(150)    NOT NULL,
    immagine    VARCHAR(255)    DEFAULT NULL,
    rilevanza   DECIMAL(3,1)    DEFAULT NULL,
    tipo        ENUM('ambientale','sociale') DEFAULT NULL,
    PRIMARY KEY (nome),
    CONSTRAINT chk_rilevanza CHECK (rilevanza BETWEEN 0 AND 10)
) ENGINE=InnoDB;

-- 11. INDICATORI_AMBIENTALI
-- Sotto-entita' di indicatori_esg (tipo = 'ambientale').
-- Campo aggiuntivo: codice normativa di rilevamento.
CREATE TABLE indicatori_ambientali (
    nome                VARCHAR(150)    NOT NULL,
    codice_normativa    VARCHAR(100)    NOT NULL,
    PRIMARY KEY (nome),
    FOREIGN KEY (nome) REFERENCES indicatori_esg(nome) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 12. INDICATORI_SOCIALI
-- Sotto-entita' di indicatori_esg (tipo = 'sociale').
-- Campi aggiuntivi: ambito sociale e frequenza di rilevazione.
CREATE TABLE indicatori_sociali (
    nome                    VARCHAR(150)    NOT NULL,
    ambito_sociale          VARCHAR(150)    NOT NULL,
    frequenza_rilevazione   VARCHAR(100)    NOT NULL,
    PRIMARY KEY (nome),
    FOREIGN KEY (nome) REFERENCES indicatori_esg(nome) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 13. VOCI_INDICATORI
-- Collegamento tra voce contabile di un bilancio e indicatore ESG.
-- Per ogni coppia <voce, indicatore> si memorizzano valore, fonte, data.
CREATE TABLE voci_indicatori (
    id_bilancio         INT             NOT NULL,
    nome_voce           VARCHAR(150)    NOT NULL,
    nome_indicatore     VARCHAR(150)    NOT NULL,
    valore_indicatore   DECIMAL(15,2)   NOT NULL,
    fonte               VARCHAR(255)    NOT NULL,
    data_rilevazione    DATE            NOT NULL,
    PRIMARY KEY (id_bilancio, nome_voce, nome_indicatore),
    FOREIGN KEY (id_bilancio, nome_voce) REFERENCES valori_bilancio(id_bilancio, nome_voce) ON DELETE CASCADE,
    FOREIGN KEY (nome_indicatore) REFERENCES indicatori_esg(nome) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 14. REVISIONI
-- Associazione revisore-bilancio (N:M).
-- Un bilancio puo' essere valutato da piu' revisori.
CREATE TABLE revisioni (
    username_revisore   VARCHAR(50) NOT NULL,
    id_bilancio         INT         NOT NULL,
    PRIMARY KEY (username_revisore, id_bilancio),
    FOREIGN KEY (username_revisore) REFERENCES revisori(username) ON DELETE CASCADE,
    FOREIGN KEY (id_bilancio)       REFERENCES bilanci(id)       ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15. NOTE_REVISIONE
-- Note del revisore su singole voci di bilancio.
-- Ogni nota ha una data e un campo testo.
CREATE TABLE note_revisione (
    id                  INT             NOT NULL AUTO_INCREMENT,
    username_revisore   VARCHAR(50)     NOT NULL,
    id_bilancio         INT             NOT NULL,
    nome_voce           VARCHAR(150)    NOT NULL,
    data_nota           DATE            NOT NULL,
    testo               TEXT            NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (username_revisore, id_bilancio)
        REFERENCES revisioni(username_revisore, id_bilancio) ON DELETE CASCADE,
    FOREIGN KEY (nome_voce) REFERENCES voci_contabili(nome) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 16. GIUDIZI
-- Giudizio complessivo del revisore su un bilancio.
-- Esito: approvazione | approvazione_con_rilievi | respingimento
CREATE TABLE giudizi (
    username_revisore   VARCHAR(50)     NOT NULL,
    id_bilancio         INT             NOT NULL,
    esito               ENUM('approvazione','approvazione_con_rilievi','respingimento') NOT NULL,
    data_giudizio       DATE            NOT NULL,
    rilievi             TEXT            DEFAULT NULL,
    PRIMARY KEY (username_revisore, id_bilancio),
    FOREIGN KEY (username_revisore, id_bilancio)
        REFERENCES revisioni(username_revisore, id_bilancio) ON DELETE CASCADE
) ENGINE=InnoDB;
