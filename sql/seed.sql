-- ESG-BALANCE: Dati di popolamento per la demo
-- Password per tutti gli utenti: password123
-- Hash bcrypt generato con cost 12
-- Hash: $2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq

USE esg_balance;

-- UTENTI (1 admin, 2 revisori, 2 responsabili)
INSERT INTO utenti (username, password_hash, codice_fiscale, data_nascita, luogo_nascita, ruolo) VALUES
('f.montanari', '$2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq', 'MNTFNC78P14A944J', '1978-09-14', 'Bologna',   'amministratore'),
('m.conti',     '$2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq', 'CNTMRC91H22F205B', '1991-06-22', 'Milano',    'revisore'),
('s.ferrara',   '$2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq', 'FRRSRA86S43H501W', '1986-11-03', 'Roma',      'revisore'),
('a.pellegrini','$2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq', 'PLLNDR89C17F257L', '1989-03-17', 'Modena',    'responsabile'),
('l.damico',    '$2y$12$1CYLwUYyfWrDN2LAhaZuoOv.w2y3PYfEb3e6YBUcwuNmdeIXilgrq', 'DMCLCU84L28F839R', '1984-07-28', 'Napoli',    'responsabile');

-- EMAIL UTENTI (uno o piu' recapiti per utente)
-- Alcuni ne hanno piu' di una per dimostrare l'attributo multivalore.
INSERT INTO email_utente (username, email) VALUES
('f.montanari', 'f.montanari@unibo.it'),
('m.conti',     'marco.conti@studioconti.it'),
('m.conti',     'marcoconti@pec.it'),
('s.ferrara',   'ferrara.sara@outlook.it'),
('a.pellegrini','a.pellegrini@fonderiabertoni.it'),
('a.pellegrini','apellegrini@pec.it'),
('l.damico',    'luca.damico@cantieridamico.it');

-- REVISORI (sotto-entita')
-- nr_revisioni viene impostato qui manualmente perche' le INSERT
-- dirette in revisioni (sotto) bypassano la SP che lo incrementa.
INSERT INTO revisori (username, nr_revisioni, indice_affidabilita) VALUES
('m.conti',   3, 0.00),
('s.ferrara', 3, 0.00);

-- RESPONSABILI (sotto-entita')
INSERT INTO responsabili (username, curriculum_pdf) VALUES
('a.pellegrini', NULL),
('l.damico',     NULL);

-- COMPETENZE REVISORI
INSERT INTO competenze_revisore (username, nome_competenza, livello) VALUES
('m.conti',   'Valutazione del rischio',      4),
('m.conti',   'Sostenibilita\' ambientale',   5),
('m.conti',   'Analisi di bilancio',          3),
('s.ferrara', 'Reporting ESG',                4),
('s.ferrara', 'Governance aziendale',         3),
('s.ferrara', 'Normativa ambientale UE',      5);

-- AZIENDE (3 aziende, 2 responsabili)
-- nr_bilanci viene aggiornato automaticamente dal trigger T3.
INSERT INTO aziende (nome, ragione_sociale, partita_iva, settore, num_dipendenti, logo, nr_bilanci, username_responsabile) VALUES
('Fonderia Bertoni', 'Fonderia Bertoni S.r.l.',       '02847190375', 'Metallurgia',     87,  NULL, 0, 'a.pellegrini'),
('Cantieri Damico',  'Cantieri Damico S.p.A.',         '07193824016', 'Cantieristica',  214,  NULL, 0, 'l.damico'),
('Agri Valsamoggia', 'Agri Valsamoggia S.r.l.',        '03561827904', 'Agroalimentare',  42,  NULL, 0, 'a.pellegrini');

-- VOCI CONTABILI (template condiviso)
INSERT INTO voci_contabili (nome, descrizione) VALUES
('Ricavi vendite',        'Totale dei ricavi derivanti dalla vendita di beni e servizi'),
('Costo del personale',   'Stipendi, contributi previdenziali e TFR'),
('Costi materie prime',   'Acquisto di materie prime e semilavorati'),
('Ammortamenti',          'Quote di ammortamento dei cespiti materiali e immateriali'),
('Debiti verso fornitori','Importo totale dei debiti commerciali verso fornitori'),
('Utile di esercizio',    'Risultato netto dopo imposte');

-- INDICATORI ESG

-- Indicatori ambientali
INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Emissioni CO2',              NULL, 9.5, 'ambientale'),
('Consumo energia elettrica',  NULL, 8.0, 'ambientale'),
('Utilizzo acqua industriale', NULL, 7.5, 'ambientale');

INSERT INTO indicatori_ambientali (nome, codice_normativa) VALUES
('Emissioni CO2',              'Reg. UE 2023/956'),
('Consumo energia elettrica',  'Dir. UE 2023/1791'),
('Utilizzo acqua industriale', 'Dir. UE 2020/2184');

-- Indicatori sociali
INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Ore formazione dipendenti',  NULL, 6.5, 'sociale'),
('Tasso infortuni',            NULL, 8.5, 'sociale');

INSERT INTO indicatori_sociali (nome, ambito_sociale, frequenza_rilevazione) VALUES
('Ore formazione dipendenti',  'Formazione e sviluppo professionale', 'Annuale'),
('Tasso infortuni',            'Salute e sicurezza sul lavoro',       'Trimestrale');

-- Indicatore di governance (senza sotto-tabella dedicata)
INSERT INTO indicatori_esg (nome, immagine, rilevanza, tipo) VALUES
('Indice diversita\' CdA',    NULL, 5.5, 'governance');

-- BILANCI DI ESERCIZIO
-- Il trigger T3 aggiorna automaticamente nr_bilanci nelle aziende.

-- Fonderia Bertoni: 3 bilanci (2023, 2024, 2025)
INSERT INTO bilanci (id_azienda, anno, data_creazione, stato) VALUES
(1, 2023, '2023-04-28', 'bozza'),
(1, 2024, '2024-04-14', 'bozza'),
(1, 2025, '2025-03-31', 'bozza');

-- Cantieri Damico: 2 bilanci (2023, 2024)
INSERT INTO bilanci (id_azienda, anno, data_creazione, stato) VALUES
(2, 2023, '2023-06-03', 'bozza'),
(2, 2024, '2024-05-22', 'bozza');

-- Agri Valsamoggia: 1 bilancio (2024)
INSERT INTO bilanci (id_azienda, anno, data_creazione, stato) VALUES
(3, 2024, '2024-07-11', 'bozza');

-- VALORI BILANCIO (voci contabili valorizzate)
-- Importi espressi in euro.

-- Bilancio #1 (Fonderia Bertoni 2023)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(1, 'Ricavi vendite',        4837250.00),
(1, 'Costo del personale',   1652800.00),
(1, 'Costi materie prime',   1073400.00),
(1, 'Ammortamenti',           318500.00),
(1, 'Debiti verso fornitori',  587300.00),
(1, 'Utile di esercizio',     391750.00);

-- Bilancio #2 (Fonderia Bertoni 2024)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(2, 'Ricavi vendite',        5214600.00),
(2, 'Costo del personale',   1718350.00),
(2, 'Costi materie prime',   1145900.00),
(2, 'Ammortamenti',           341200.00),
(2, 'Debiti verso fornitori',  512700.00),
(2, 'Utile di esercizio',     467850.00);

-- Bilancio #3 (Fonderia Bertoni 2025) - in bozza, compilato solo in parte
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(3, 'Ricavi vendite',        5580000.00),
(3, 'Costo del personale',   1825400.00),
(3, 'Costi materie prime',   1197300.00),
(3, 'Ammortamenti',           362000.00);

-- Bilancio #4 (Cantieri Damico 2023)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(4, 'Ricavi vendite',       11473800.00),
(4, 'Costo del personale',   4128500.00),
(4, 'Costi materie prime',   2964700.00),
(4, 'Ammortamenti',           763200.00),
(4, 'Debiti verso fornitori', 1987400.00),
(4, 'Utile di esercizio',     312600.00);

-- Bilancio #5 (Cantieri Damico 2024)
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(5, 'Ricavi vendite',       12835400.00),
(5, 'Costo del personale',   4391700.00),
(5, 'Costi materie prime',   3247100.00),
(5, 'Ammortamenti',           812500.00),
(5, 'Debiti verso fornitori', 1743600.00),
(5, 'Utile di esercizio',     493200.00);

-- Bilancio #6 (Agri Valsamoggia 2024) - in bozza, con tutte le voci
INSERT INTO valori_bilancio (id_bilancio, nome_voce, valore) VALUES
(6, 'Ricavi vendite',        2916300.00),
(6, 'Costo del personale',    874500.00),
(6, 'Costi materie prime',    638700.00),
(6, 'Ammortamenti',           175200.00),
(6, 'Debiti verso fornitori',  421800.00),
(6, 'Utile di esercizio',     218900.00);

-- INDICATORI ESG COLLEGATI ALLE VOCI DI BILANCIO

-- Bilancio #1 (Fonderia Bertoni 2023)
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(1, 'Costi materie prime',  'Consumo energia elettrica',  273480.00, 'Registro consumi ARERA',  '2023-12-31'),
(1, 'Costi materie prime',  'Utilizzo acqua industriale',  18740.00, 'Lettura contatore',       '2023-12-31'),
(1, 'Costo del personale',  'Ore formazione dipendenti',      28.00, 'Rapporto formazione RU',  '2023-12-31');

-- Bilancio #2 (Fonderia Bertoni 2024)
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(2, 'Costi materie prime',  'Consumo energia elettrica',  251300.00, 'Registro consumi ARERA',      '2024-12-31'),
(2, 'Costi materie prime',  'Emissioni CO2',                 137.50, 'Certificazione ISO 14064',    '2024-12-31'),
(2, 'Costo del personale',  'Ore formazione dipendenti',      35.00, 'Rapporto formazione RU',      '2024-12-31'),
(2, 'Costo del personale',  'Tasso infortuni',                 1.80, 'Denuncia INAIL annuale',      '2024-09-30');

-- Bilancio #4 (Cantieri Damico 2023)
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(4, 'Costi materie prime',  'Emissioni CO2',                1187.00, 'Perizia ambientale ARPA',     '2023-12-31'),
(4, 'Costi materie prime',  'Consumo energia elettrica',  463200.00, 'Fatture fornitore elettrico', '2023-12-31');

-- Bilancio #5 (Cantieri Damico 2024)
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(5, 'Costi materie prime',  'Consumo energia elettrica',  498750.00, 'Fatture fornitore elettrico', '2024-12-31'),
(5, 'Costi materie prime',  'Emissioni CO2',                 943.00, 'Perizia ambientale ARPA',     '2024-12-31');

-- Bilancio #6 (Agri Valsamoggia 2024) - in bozza
INSERT INTO voci_indicatori (id_bilancio, nome_voce, nome_indicatore, valore_indicatore, fonte, data_rilevazione) VALUES
(6, 'Costi materie prime',  'Consumo energia elettrica',  128400.00, 'Bollette e-distribuzione',    '2024-12-31'),
(6, 'Costi materie prime',  'Emissioni CO2',                  39.20, 'Dichiarazione ISPRA',         '2024-12-31'),
(6, 'Costo del personale',  'Ore formazione dipendenti',      44.00, 'Rapporto formazione RU',      '2024-12-31');

-- REVISIONI (assegnazione revisori ai bilanci)
-- Il trigger T1 cambia lo stato a 'in_revisione'.
-- Le INSERT dirette non passano dalla SP, quindi nr_revisioni
-- e' stato impostato manualmente nella tabella revisori.
INSERT INTO revisioni (username_revisore, id_bilancio) VALUES
('m.conti',   1),
('s.ferrara', 1),
('m.conti',   2),
('m.conti',   4),
('s.ferrara', 4),
('s.ferrara', 5);

-- NOTE REVISIONE
INSERT INTO note_revisione (username_revisore, id_bilancio, nome_voce, data_nota, testo) VALUES
('m.conti',   1, 'Costi materie prime',  '2023-05-18', 'I costi risultano allineati al report consumi. Nulla da segnalare.'),
('m.conti',   2, 'Costi materie prime',  '2024-06-02', 'Riscontrata discrepanza tra il valore delle materie prime e il consumo energetico dichiarato, da approfondire.'),
('s.ferrara', 1, 'Utile di esercizio',   '2023-05-21', 'Il margine netto e\' nella media del comparto metalmeccanico emiliano.'),
('s.ferrara', 4, 'Costi materie prime',  '2023-07-14', 'Non sono presenti dati sul consumo idrico del cantiere. Richiedere documentazione.'),
('s.ferrara', 4, 'Costo del personale',  '2023-07-14', 'Manca il collegamento con indicatori sociali. Chiedere al responsabile di integrare ore formazione e infortuni.');

-- GIUDIZI COMPLESSIVI
-- Il trigger T2 aggiorna lo stato del bilancio quando tutti
-- i revisori assegnati hanno espresso il giudizio.

-- Bilancio #1 (Fonderia Bertoni 2023): 2 revisori, entrambi approvano => 'approvato'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('m.conti',   1, 'approvazione', '2023-06-05', NULL),
('s.ferrara', 1, 'approvazione', '2023-06-08', NULL);

-- Bilancio #2 (Fonderia Bertoni 2024): 1 revisore, approva con rilievi => 'approvato'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('m.conti', 2, 'approvazione_con_rilievi', '2024-06-18', 'Servono dettagli piu\' precisi sulle emissioni di CO2 dello stabilimento.');

-- Bilancio #4 (Cantieri Damico 2023): 2 revisori, 1 respinge => 'respinto'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('m.conti',   4, 'approvazione',  '2023-08-02', NULL),
('s.ferrara', 4, 'respingimento', '2023-08-05', 'Documentazione ambientale carente: mancano dati idrici e indicatori sociali.');

-- Bilancio #5 (Cantieri Damico 2024): 1 revisore, approvazione => 'approvato'
INSERT INTO giudizi (username_revisore, id_bilancio, esito, data_giudizio, rilievi) VALUES
('s.ferrara', 5, 'approvazione', '2024-08-12', NULL);
