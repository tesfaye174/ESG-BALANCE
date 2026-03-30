-- Schema del database ESG-BALANCE
-- Basi di Dati, A.A. 2025/2026

DROP DATABASE IF EXISTS esg_balance;
CREATE DATABASE esg_balance
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE esg_balance;

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

CREATE TABLE email_utente (
    username    VARCHAR(50)     NOT NULL,
    email       VARCHAR(150)    NOT NULL,
    PRIMARY KEY (username, email),
    FOREIGN KEY (username) REFERENCES utenti(username) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE revisori (
    username            VARCHAR(50)     NOT NULL,
    nr_revisioni        INT             DEFAULT 0,
    indice_affidabilita DECIMAL(3,2)    DEFAULT 0.00,
    PRIMARY KEY (username),
    FOREIGN KEY (username) REFERENCES utenti(username) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE responsabili (
    username VARCHAR(50) NOT NULL,
    curriculum_pdf VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (username),
    FOREIGN KEY (username) REFERENCES utenti(username) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE competenze_revisore (
    username            VARCHAR(50)     NOT NULL,
    nome_competenza     VARCHAR(100)    NOT NULL,
    livello             TINYINT         NOT NULL,
    PRIMARY KEY (username, nome_competenza),
    FOREIGN KEY (username) REFERENCES revisori(username) ON DELETE CASCADE,
    CONSTRAINT chk_livello CHECK (livello BETWEEN 0 AND 5)
) ENGINE=InnoDB;

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

-- template bilancio
CREATE TABLE voci_contabili (
    nome        VARCHAR(150)    NOT NULL,
    descrizione TEXT            DEFAULT NULL,
    PRIMARY KEY (nome)
) ENGINE=InnoDB;

-- un bilancio per azienda per anno, stato gestito dai trigger
CREATE TABLE bilanci (
    id              INT     NOT NULL AUTO_INCREMENT,
    id_azienda      INT     NOT NULL,
    anno            YEAR    NOT NULL,
    data_creazione  DATE    NOT NULL,
    stato           ENUM('bozza','in_revisione','approvato','respinto') DEFAULT 'bozza',
    PRIMARY KEY (id),
    UNIQUE KEY (id_azienda, anno),
    FOREIGN KEY (id_azienda) REFERENCES aziende(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE valori_bilancio (
    id_bilancio INT             NOT NULL,
    nome_voce   VARCHAR(150)    NOT NULL,
    valore      DECIMAL(15,2)   NOT NULL,
    PRIMARY KEY (id_bilancio, nome_voce),
    FOREIGN KEY (id_bilancio) REFERENCES bilanci(id) ON DELETE CASCADE,
    FOREIGN KEY (nome_voce)   REFERENCES voci_contabili(nome) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE indicatori_esg (
    nome        VARCHAR(150)    NOT NULL,
    immagine    VARCHAR(255)    DEFAULT NULL,
    rilevanza   DECIMAL(3,1)    DEFAULT NULL,
    tipo        ENUM('ambientale','sociale','governance') DEFAULT NULL,
    PRIMARY KEY (nome),
    CONSTRAINT chk_rilevanza CHECK (rilevanza BETWEEN 0 AND 10)
) ENGINE=InnoDB;

CREATE TABLE indicatori_ambientali (
    nome VARCHAR(150) NOT NULL,
    codice_normativa VARCHAR(100) NOT NULL,
    PRIMARY KEY (nome),
    FOREIGN KEY (nome) REFERENCES indicatori_esg(nome) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE indicatori_sociali (
    nome                    VARCHAR(150)    NOT NULL,
    ambito_sociale          VARCHAR(150)    NOT NULL,
    frequenza_rilevazione   VARCHAR(100)    NOT NULL,
    PRIMARY KEY (nome),
    FOREIGN KEY (nome) REFERENCES indicatori_esg(nome) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

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

CREATE TABLE revisioni (
    username_revisore   VARCHAR(50) NOT NULL,
    id_bilancio         INT         NOT NULL,
    PRIMARY KEY (username_revisore, id_bilancio),
    FOREIGN KEY (username_revisore) REFERENCES revisori(username) ON DELETE CASCADE,
    FOREIGN KEY (id_bilancio)       REFERENCES bilanci(id)       ON DELETE CASCADE
) ENGINE=InnoDB;

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
    FOREIGN KEY (id_bilancio, nome_voce)
        REFERENCES valori_bilancio(id_bilancio, nome_voce) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ridondanza nr_bilanci (vedi relazione)
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

-- fallback log su mysql se mongodb è offline
CREATE TABLE log_eventi (
    id          INT             NOT NULL AUTO_INCREMENT,
    evento      VARCHAR(100)    NOT NULL,
    utente      VARCHAR(50)     DEFAULT NULL,
    dettagli    TEXT            DEFAULT NULL,
    timestamp   DATETIME        NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE INDEX idx_bilanci_stato               ON bilanci(stato);
CREATE INDEX idx_email_utente_username       ON email_utente(username);
CREATE INDEX idx_valori_bilancio_bilancio    ON valori_bilancio(id_bilancio);
CREATE INDEX idx_voci_indicatori_bilancio    ON voci_indicatori(id_bilancio);
CREATE INDEX idx_revisioni_revisore          ON revisioni(username_revisore);
CREATE INDEX idx_note_revisione_bilancio     ON note_revisione(id_bilancio);
CREATE INDEX idx_giudizi_bilancio            ON giudizi(id_bilancio);
