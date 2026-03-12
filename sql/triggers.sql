-- ESG-BALANCE: Trigger

USE esg_balance;

DELIMITER $$

-- TRIGGER T1: Cambio stato bilancio a 'in_revisione'
-- Attivato quando un revisore ESG viene assegnato a un bilancio.
-- Se il bilancio e' ancora in stato 'bozza', diventa 'in_revisione'.
CREATE TRIGGER trg_bilancio_in_revisione
AFTER INSERT ON revisioni
FOR EACH ROW
BEGIN
    UPDATE bilanci
    SET stato = 'in_revisione'
    WHERE id = NEW.id_bilancio
      AND stato = 'bozza';
END$$

-- TRIGGER T2: Cambio stato bilancio a 'approvato' o 'respinto'
-- Attivato quando un revisore inserisce un giudizio.
-- Se TUTTI i revisori associati al bilancio hanno inserito
-- il loro giudizio:
--   - Se tutti sono 'approvazione' o 'approvazione_con_rilievi'
--     => stato = 'approvato'
--   - Se almeno uno e' 'respingimento'
--     => stato = 'respinto'
CREATE TRIGGER trg_bilancio_giudizio
AFTER INSERT ON giudizi
FOR EACH ROW
BEGIN
    DECLARE v_tot_revisori INT;
    DECLARE v_tot_giudizi  INT;
    DECLARE v_respingimenti INT;

    -- Conto quanti revisori sono assegnati a questo bilancio
    SELECT COUNT(*) INTO v_tot_revisori
    FROM revisioni
    WHERE id_bilancio = NEW.id_bilancio;

    -- Conto quanti giudizi sono stati emessi per questo bilancio
    SELECT COUNT(*) INTO v_tot_giudizi
    FROM giudizi
    WHERE id_bilancio = NEW.id_bilancio;

    -- Se tutti hanno votato, determino l'esito finale
    IF v_tot_giudizi = v_tot_revisori THEN
        -- Conto i respingimenti
        SELECT COUNT(*) INTO v_respingimenti
        FROM giudizi
        WHERE id_bilancio = NEW.id_bilancio
          AND esito = 'respingimento';

        IF v_respingimenti > 0 THEN
            UPDATE bilanci SET stato = 'respinto'  WHERE id = NEW.id_bilancio;
        ELSE
            UPDATE bilanci SET stato = 'approvato'  WHERE id = NEW.id_bilancio;
        END IF;
    END IF;

    -- Aggiorno l'indice di affidabilita' del revisore che ha votato
    CALL sp_aggiorna_indice_affidabilita(NEW.username_revisore);
END$$

-- TRIGGER T3: Incremento ridondanza nr_bilanci
-- Quando viene creato un nuovo bilancio per un'azienda,
-- il contatore nr_bilanci viene incrementato di 1.
-- (Gestione della ridondanza concettuale)
CREATE TRIGGER trg_incrementa_nr_bilanci
AFTER INSERT ON bilanci
FOR EACH ROW
BEGIN
    UPDATE aziende
    SET nr_bilanci = nr_bilanci + 1
    WHERE id = NEW.id_azienda;
END$$

-- TRIGGER T4: Decremento ridondanza nr_bilanci
-- Quando viene eliminato un bilancio, il contatore viene
-- decrementato di 1.
CREATE TRIGGER trg_decrementa_nr_bilanci
AFTER DELETE ON bilanci
FOR EACH ROW
BEGIN
    UPDATE aziende
    SET nr_bilanci = nr_bilanci - 1
    WHERE id = OLD.id_azienda;
END$$

DELIMITER ;
