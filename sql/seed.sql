-- Dati di popolamento
-- admin: password "admin" (bcrypt cost 12)
-- tesfaye, giovanni: password "password123" (bcrypt cost 12)

USE esg_balance;

INSERT INTO utenti (username, password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo) VALUES
('admin',    '$2y$12$6XH.1B8i/ynz7hBgMQ80EuJ.EmOQYF3CKZKFZiQ8od3iAyoyMAZji', 'DMNRSS80A01H501Z', '1980-01-01', 'Roma',   'amministratore'),
('tesfaye',  '$2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq', 'TSFYXX90B14Z330X', '1990-02-14', 'Milano', 'revisore'),
('giovanni', '$2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq', 'GVNRSS88C10F205W', '1988-03-10', 'Torino', 'responsabile');

INSERT INTO email_utente (username, email) VALUES
('admin',    'admin@esgbalance.it'),
('tesfaye',  'tesfaye@esgbalance.it'),
('giovanni', 'giovanni@esgbalance.it');

-- nr_revisioni impostato a mano perche' le insert dirette bypassano la SP
INSERT INTO revisori (username, nr_revisioni, indice_affidabilita) VALUES
('tesfaye', 2, 0.50);

INSERT INTO responsabili (username, curriculum_pdf) VALUES
('giovanni', NULL);

INSERT INTO competenze_revisore (username, nome_competenza, livello) VALUES
('tesfaye', 'Valutazione del rischio',      4),
('tesfaye', 'Sostenibilita\' ambientale',   5),
('tesfaye', 'Analisi di bilancio',          3),
('tesfaye', 'Reporting ESG',                4),
('tesfaye', 'Governance aziendale',         3),
('tesfaye', 'Normativa ambientale UE',      5);

INSERT INTO aziende (nome, ragione_sociale, partita_iva, settore, num_dipendenti, logo, nr_bilanci, username_responsabile) VALUES
('Fonderia Bertoni', 'Fonderia Bertoni S.r.l.', '02847190375', 'Metallurgia',    87,  NULL, 0, 'giovanni'),
('Cantieri Damico',  'Cantieri Damico S.p.A.',  '07193824016', 'Cantieristica', 214,  NULL, 0, 'giovanni');

INSERT INTO voci_contabili (nome, descrizione) VALUES
('Ricavi vendite',        'Totale dei ricavi derivanti dalla vendita di beni e servizi'),
('Costo del personale',   'Stipendi, contributi previdenziali e TFR'),
('Costi materie prime',   'Acquisto di materie prime e semilavorati'),
('Ammortamenti',          'Quote di ammortamento dei cespiti materiali e immateriali'),
('Debiti verso fornitori','Importo totale dei debiti commerciali verso fornitori'),
('Utile di esercizio',    'Risultato netto dopo imposte');

-- indicatori ambientali
INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Emissioni CO2',              NULL, 9.5, 'ambientale'),
('Consumo energia elettrica',  NULL, 8.0, 'ambientale'),
('Utilizzo acqua industriale', NULL, 7.5, 'ambientale');

INSERT INTO indicatori_ambientali (nome, codice_normativa) VALUES
('Emissioni CO2',              'Reg. UE 2023/956'),
('Consumo energia elettrica',  'Dir. UE 2023/1791'),
('Utilizzo acqua industriale', 'Dir. UE 2020/2184');

INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Ore formazione dipendenti',  NULL, 6.5, 'sociale'),
('Tasso infortuni',            NULL, 8.5, 'sociale');

INSERT INTO indicatori_sociali (nome, ambito_sociale, frequenza_rilevazione) VALUES
('Ore formazione dipendenti',  'Formazione e sviluppo professionale', 'Annuale'),
('Tasso infortuni',            'Salute e sicurezza sul lavoro',       'Trimestrale');

INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Indice diversita\' CdA', NULL, 5.5, 'governance');

-- bilancio 1: Fonderia Bertoni 2023 — completo, revisionato e approvato
INSERT INTO bilanci (id_azienda, anno, data_creazione, stato) VALUES
(1, 2023, '2023-04-28', 'bozza');

-- bilancio 2: Fonderia Bertoni 2024 — bozza in corso, valori parziali
INSERT INTO bilanci (id_azienda, anno, data_creazione, stato) VALUES
(1, 2024, '2024-04-14', 'bozza');

-- bilancio 3: Cantieri Damico 2023 — completo, revisionato con rilievi
INSERT INTO bilanci (id_azienda, anno, data_creazione, stato) VALUES
(2, 2023, '2023-06-03', 'bozza');

-- valori bilancio 1 (Fonderia 2023) — completo
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(1, 'Ricavi vendite',         4837250.00),
(1, 'Costo del personale',    1652800.00),
(1, 'Costi materie prime',    1073400.00),
(1, 'Ammortamenti',            318500.00),
(1, 'Debiti verso fornitori',  587300.00),
(1, 'Utile di esercizio',      391750.00);

-- valori bilancio 2 (Fonderia 2024) — bozza, compilato solo in parte
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(2, 'Ricavi vendite',      5214600.00),
(2, 'Costo del personale', 1718350.00),
(2, 'Costi materie prime', 1145900.00),
(2, 'Ammortamenti',         341200.00);

-- valori bilancio 3 (Cantieri Damico 2023) — completo
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(3, 'Ricavi vendite',        11473800.00),
(3, 'Costo del personale',    4128500.00),
(3, 'Costi materie prime',    2964700.00),
(3, 'Ammortamenti',            763200.00),
(3, 'Debiti verso fornitori', 1987400.00),
(3, 'Utile di esercizio',      312600.00);

INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(1, 'Costi materie prime', 'Consumo energia elettrica',  273480.00, 'Registro consumi ARERA', '2023-12-31'),
(1, 'Costi materie prime', 'Utilizzo acqua industriale',  18740.00, 'Lettura contatore',      '2023-12-31'),
(1, 'Costo del personale', 'Ore formazione dipendenti',      28.00, 'Rapporto formazione RU', '2023-12-31');

INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(3, 'Costi materie prime', 'Emissioni CO2',               1187.00, 'Perizia ambientale ARPA',     '2023-12-31'),
(3, 'Costi materie prime', 'Consumo energia elettrica',  463200.00, 'Fatture fornitore elettrico', '2023-12-31');

-- le insert dirette in revisioni non passano dalla SP, nr_revisioni impostato a mano sopra
INSERT INTO revisioni (username_revisore, id_bilancio) VALUES
('tesfaye', 1),
('tesfaye', 3);

INSERT INTO note_revisione (username_revisore, id_bilancio, nome_voce, data_nota, testo) VALUES
('tesfaye', 1, 'Costi materie prime', '2023-05-18', 'I costi risultano allineati al report consumi. Nulla da segnalare.'),
('tesfaye', 1, 'Utile di esercizio',  '2023-05-21', 'Il margine netto e\' nella media del comparto metalmeccanico emiliano.'),
('tesfaye', 3, 'Costi materie prime', '2023-07-14', 'Non sono presenti dati sul consumo idrico del cantiere. Richiedere documentazione.'),
('tesfaye', 3, 'Costo del personale', '2023-07-14', 'Manca il collegamento con indicatori sociali. Chiedere al responsabile di integrare ore formazione e infortuni.');

INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('tesfaye', 1, 'approvazione', '2023-06-05', NULL);

INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('tesfaye', 3, 'approvazione_con_rilievi', '2023-08-02', 'Documentazione ambientale carente: mancano dati idrici e indicatori sociali.');

-- i trigger T1 e T2 scattano anche sulle INSERT dirette, quindi gli stati
-- sono già stati aggiornati automaticamente. Queste UPDATE sono ridondanti
-- ma lasciate per sicurezza in caso si reimporti solo il seed senza trigger
UPDATE bilanci SET stato = 'in_revisione' WHERE id IN (1, 3);
UPDATE bilanci SET stato = 'approvato'    WHERE id IN (1, 3);
