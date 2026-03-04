-- ============================================================
-- ESG-BALANCE: Dati di popolamento per la demo
-- Password per tutti gli utenti: password123
-- Hash bcrypt: $2y$10$60cqimkQSkez/wQ7GGQp7u0sxvW1NJiKIqFRtQlWW.d.bbgd6YcsS
-- ============================================================

USE esg_balance;

-- ============================================================
-- UTENTI (1 admin, 2 revisori, 2 responsabili)
-- ============================================================
INSERT INTO utenti (username, password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo) VALUES
('admin1',      '$2y$10$60cqimkQSkez/wQ7GGQp7u0sxvW1NJiKIqFRtQlWW.d.bbgd6YcsS', 'DMNGPP85A01A944X', '1985-01-01', 'Bologna',  'amministratore'),
('rev_marco',   '$2y$10$60cqimkQSkez/wQ7GGQp7u0sxvW1NJiKIqFRtQlWW.d.bbgd6YcsS', 'RSSMRC90B15F205Z', '1990-02-15', 'Milano',   'revisore'),
('rev_laura',   '$2y$10$60cqimkQSkez/wQ7GGQp7u0sxvW1NJiKIqFRtQlWW.d.bbgd6YcsS', 'VRDLRA88C45H501P', '1988-03-05', 'Roma',     'revisore'),
('resp_giulia', '$2y$10$60cqimkQSkez/wQ7GGQp7u0sxvW1NJiKIqFRtQlWW.d.bbgd6YcsS', 'BNCGLA92D50A944T', '1992-04-10', 'Bologna',  'responsabile'),
('resp_paolo',  '$2y$10$60cqimkQSkez/wQ7GGQp7u0sxvW1NJiKIqFRtQlWW.d.bbgd6YcsS', 'NRIPLP87E20F839K', '1987-05-20', 'Napoli',   'responsabile');

-- ============================================================
-- EMAIL UTENTI (uno o piu' recapiti per utente)
-- ============================================================
INSERT INTO email_utente (username, email) VALUES
('admin1',      'admin@esgbalance.it'),
('rev_marco',   'marco.rossi@email.it'),
('rev_laura',   'laura.verdi@email.it'),
('resp_giulia', 'giulia.bianchi@greentech.it'),
('resp_paolo',  'paolo.neri@ecobuild.it');

-- ============================================================
-- REVISORI (sotto-entita')
-- ============================================================
INSERT INTO revisori (username, nr_revisioni, indice_affidabilita) VALUES
('rev_marco', 0, 0.00),
('rev_laura', 0, 0.00);

-- ============================================================
-- RESPONSABILI (sotto-entita')
-- ============================================================
INSERT INTO responsabili (username, curriculum_pdf) VALUES
('resp_giulia', NULL),
('resp_paolo',  NULL);

-- ============================================================
-- COMPETENZE REVISORI
-- ============================================================
INSERT INTO competenze_revisore (username, nome_competenza, livello) VALUES
('rev_marco', 'Risk Assessment',            4),
('rev_marco', 'Sostenibilita ambientale',   5),
('rev_laura', 'Reporting ESG',              4),
('rev_laura', 'Governance aziendale',       3);

-- ============================================================
-- AZIENDE (3 aziende, 2 responsabili)
-- nr_bilanci viene aggiornato automaticamente dal trigger.
-- ============================================================
INSERT INTO aziende (nome, ragione_sociale, partita_iva, settore, num_dipendenti, logo, nr_bilanci, username_responsabile) VALUES
('GreenTech',  'GreenTech S.r.l.',  '01234567890', 'Tecnologia', 120, NULL, 0, 'resp_giulia'),
('EcoBuild',   'EcoBuild S.p.A.',   '09876543210', 'Edilizia',   250, NULL, 0, 'resp_paolo'),
('SolarPlus',  'SolarPlus S.r.l.',  '05678901234', 'Energia',     80, NULL, 0, 'resp_giulia');

-- ============================================================
-- VOCI CONTABILI (template condiviso)
-- ============================================================
INSERT INTO voci_contabili (nome, descrizione) VALUES
('Ricavi vendite',       'Totale dei ricavi derivanti dalla vendita di beni e servizi'),
('Costo del personale',  'Stipendi, contributi e TFR'),
('Costi materie prime',  'Acquisto di materie prime e semilavorati'),
('Ammortamenti',         'Quote di ammortamento dei beni materiali e immateriali'),
('Debiti verso fornitori','Importo totale dei debiti commerciali verso fornitori'),
('Utile di esercizio',   'Risultato netto dopo imposte');

-- ============================================================
-- INDICATORI ESG
-- ============================================================

-- Indicatori ambientali
INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Emissioni CO2',              NULL, 9.5, 'ambientale'),
('Consumo energia elettrica',  NULL, 8.5, 'ambientale'),
('Utilizzo acqua potabile',    NULL, 7.0, 'ambientale');

INSERT INTO indicatori_ambientali (nome, codice_normativa) VALUES
('Emissioni CO2',              'UE-2023/0956'),
('Consumo energia elettrica',  'UE-2024/1275'),
('Utilizzo acqua potabile',    'UE-2020/2184');

-- Indicatori sociali
INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Ore formazione dipendenti',  NULL, 6.0, 'sociale'),
('Tasso infortuni',            NULL, 8.0, 'sociale');

INSERT INTO indicatori_sociali (nome, ambito_sociale, frequenza_rilevazione) VALUES
('Ore formazione dipendenti',  'Formazione e sviluppo', 'Annuale'),
('Tasso infortuni',            'Sicurezza sul lavoro',  'Trimestrale');

-- Indicatore generico (non ambientale ne' sociale)
INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Indice diversita CdA',       NULL, 5.5, NULL);

-- ============================================================
-- BILANCI DI ESERCIZIO
-- (il trigger aggiorna automaticamente nr_bilanci nelle aziende)
-- ============================================================

-- GreenTech: 3 bilanci
INSERT INTO bilanci (id_azienda, data_creazione, stato) VALUES
(1, '2023-04-15', 'bozza'),
(1, '2024-04-10', 'bozza'),
(1, '2025-04-08', 'bozza');

-- EcoBuild: 2 bilanci
INSERT INTO bilanci (id_azienda, data_creazione, stato) VALUES
(2, '2023-05-20', 'bozza'),
(2, '2024-05-18', 'bozza');

-- SolarPlus: 1 bilancio
INSERT INTO bilanci (id_azienda, data_creazione, stato) VALUES
(3, '2024-06-01', 'bozza');

-- ============================================================
-- VALORI BILANCIO (voci contabili valorizzate)
-- ============================================================

-- Bilancio #1 (GreenTech 2023)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(1, 'Ricavi vendite',        5200000.00),
(1, 'Costo del personale',   1800000.00),
(1, 'Costi materie prime',    980000.00),
(1, 'Ammortamenti',           350000.00),
(1, 'Debiti verso fornitori',  620000.00),
(1, 'Utile di esercizio',     450000.00);

-- Bilancio #2 (GreenTech 2024)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(2, 'Ricavi vendite',        5800000.00),
(2, 'Costo del personale',   1950000.00),
(2, 'Costi materie prime',   1050000.00),
(2, 'Ammortamenti',           380000.00),
(2, 'Debiti verso fornitori',  540000.00),
(2, 'Utile di esercizio',     520000.00);

-- Bilancio #4 (EcoBuild 2023)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(4, 'Ricavi vendite',       12000000.00),
(4, 'Costo del personale',   4500000.00),
(4, 'Costi materie prime',   3200000.00),
(4, 'Ammortamenti',           800000.00),
(4, 'Debiti verso fornitori', 2100000.00),
(4, 'Utile di esercizio',     380000.00);

-- Bilancio #5 (EcoBuild 2024)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(5, 'Ricavi vendite',       13500000.00),
(5, 'Costo del personale',   4800000.00),
(5, 'Costi materie prime',   3500000.00),
(5, 'Ammortamenti',           850000.00),
(5, 'Debiti verso fornitori', 1900000.00),
(5, 'Utile di esercizio',     550000.00);

-- ============================================================
-- INDICATORI ESG COLLEGATI ALLE VOCI DI BILANCIO
-- ============================================================

-- Bilancio #1 (GreenTech 2023)
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(1, 'Costi materie prime',  'Consumo energia elettrica', 285000.00, 'Report interno',             '2023-12-31'),
(1, 'Costi materie prime',  'Utilizzo acqua potabile',    12500.00, 'Contatore aziendale',        '2023-12-31'),
(1, 'Costo del personale',  'Ore formazione dipendenti',     32.00, 'HR Report',                  '2023-12-31');

-- Bilancio #2 (GreenTech 2024)
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(2, 'Costi materie prime',  'Consumo energia elettrica', 260000.00, 'Report interno',             '2024-12-31'),
(2, 'Costi materie prime',  'Emissioni CO2',                145.00, 'Certificazione ambientale',  '2024-12-31'),
(2, 'Costo del personale',  'Ore formazione dipendenti',     40.00, 'HR Report',                  '2024-12-31'),
(2, 'Costo del personale',  'Tasso infortuni',                1.20, 'INAIL',                      '2024-12-31');

-- Bilancio #5 (EcoBuild 2024)
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(5, 'Costi materie prime',  'Consumo energia elettrica', 520000.00, 'Bollette energetiche',       '2024-12-31'),
(5, 'Costi materie prime',  'Emissioni CO2',                890.00, 'Audit ambientale',           '2024-12-31');

-- ============================================================
-- REVISIONI (assegnazione revisori ai bilanci)
-- Il trigger trg_bilancio_in_revisione cambia lo stato a 'in_revisione'.
-- ============================================================
INSERT INTO revisioni (username_revisore, id_bilancio) VALUES
('rev_marco', 1),
('rev_laura', 1),
('rev_marco', 2),
('rev_marco', 4),
('rev_laura', 4),
('rev_laura', 5);

-- ============================================================
-- NOTE REVISIONE
-- ============================================================
INSERT INTO note_revisione (username_revisore, id_bilancio, nome_voce, data_nota, testo) VALUES
('rev_marco', 2, 'Costi materie prime', '2024-06-14', 'Verificare la coerenza tra costi materie prime e consumo energia dichiarato.'),
('rev_laura', 4, 'Costi materie prime', '2023-07-28', 'Mancano dati di dettaglio sul consumo idrico.');

-- ============================================================
-- GIUDIZI COMPLESSIVI
-- Il trigger trg_bilancio_giudizio aggiorna automaticamente
-- lo stato del bilancio quando tutti i revisori hanno votato.
-- ============================================================

-- Bilancio #1 (GreenTech 2023): 2 revisori, entrambi approvano => 'approvato'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('rev_marco', 1, 'approvazione', '2023-06-10', NULL),
('rev_laura', 1, 'approvazione', '2023-06-12', NULL);

-- Bilancio #2 (GreenTech 2024): 1 revisore, approvazione con rilievi => 'approvato'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('rev_marco', 2, 'approvazione_con_rilievi', '2024-06-15', 'Migliorare la documentazione sulle emissioni CO2.');

-- Bilancio #4 (EcoBuild 2023): 2 revisori, 1 respinge => 'respinto'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('rev_marco', 4, 'approvazione',   '2023-08-01', NULL),
('rev_laura', 4, 'respingimento',  '2023-08-03', 'Dati sugli indicatori ambientali insufficienti.');

-- Bilancio #5 (EcoBuild 2024): 1 revisore, approvazione => 'approvato'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('rev_laura', 5, 'approvazione', '2024-08-20', NULL);
