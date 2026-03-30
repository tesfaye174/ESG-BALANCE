-- Viste ESG-BALANCE

USE esg_balance;

CREATE OR REPLACE VIEW v_num_aziende AS
SELECT COUNT(*) AS totale_aziende
FROM aziende;

CREATE OR REPLACE VIEW v_num_revisori AS
SELECT COUNT(*) AS totale_revisori
FROM revisori;

-- classifica aziende per affidabilita'
CREATE OR REPLACE VIEW v_affidabilita_aziende AS
SELECT
    a.id AS id_azienda,
    a.nome,
    a.ragione_sociale,
    COUNT(DISTINCT b.id) AS bilanci_giudicati,
    COUNT(DISTINCT CASE
        WHEN NOT EXISTS (
            SELECT 1
            FROM giudizi g2
            WHERE g2.id_bilancio = b.id
              AND g2.esito <> 'approvazione'
            LIMIT 1
        )
        THEN b.id
        ELSE NULL
    END) AS bilanci_approvati_puri,
    ROUND(
        COUNT(DISTINCT CASE
            WHEN NOT EXISTS (
                SELECT 1
                FROM giudizi g2
                WHERE g2.id_bilancio = b.id
                  AND g2.esito <> 'approvazione'
                LIMIT 1
            )
            THEN b.id
            ELSE NULL
        END) * 100.0 / COUNT(DISTINCT b.id),
        2
    ) AS percentuale_affidabilita
FROM aziende a
JOIN bilanci b ON b.id_azienda = a.id
JOIN giudizi g ON g.id_bilancio = b.id
GROUP BY a.id, a.nome, a.ragione_sociale
ORDER BY percentuale_affidabilita DESC;

-- classifica bilanci per numero indicatori ESG collegati
CREATE OR REPLACE VIEW v_classifica_bilanci_esg AS
SELECT
    b.id AS id_bilancio,
    a.nome AS azienda,
    a.ragione_sociale,
    b.anno,
    b.data_creazione,
    b.stato,
    COUNT(vi.nome_indicatore) AS num_indicatori_esg
FROM bilanci b
JOIN aziende a ON a.id = b.id_azienda
LEFT JOIN voci_indicatori vi ON vi.id_bilancio = b.id
GROUP BY b.id, a.nome, a.ragione_sociale, b.anno, b.data_creazione, b.stato
ORDER BY num_indicatori_esg DESC;
