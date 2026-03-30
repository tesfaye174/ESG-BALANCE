-- Trigger ESG-BALANCE

USE esg_balance;

DELIMITER $$

-- T1: quando si assegna un revisore, il bilancio passa in revisione
CREATE TRIGGER trg_bilancio_in_revisione
AFTER INSERT ON revisioni
FOR EACH ROW
BEGIN
    UPDATE bilanci
    SET stato = 'in_revisione'
    WHERE id = NEW.id_bilancio
      AND stato = 'bozza';
END$$

-- T2: se tutti i revisori hanno giudicato, chiude il bilancio
CREATE TRIGGER trg_bilancio_giudizio
AFTER INSERT ON giudizi
FOR EACH ROW
BEGIN
    DECLARE v_tot_revisori  INT;
    DECLARE v_tot_giudizi   INT;
    DECLARE v_respingimenti INT;

    SELECT COUNT(*) INTO v_tot_revisori
    FROM revisioni
    WHERE id_bilancio = NEW.id_bilancio;

    SELECT COUNT(*) INTO v_tot_giudizi
    FROM giudizi
    WHERE id_bilancio = NEW.id_bilancio;

    IF v_tot_giudizi = v_tot_revisori THEN
        SELECT COUNT(*) INTO v_respingimenti
        FROM giudizi
        WHERE id_bilancio = NEW.id_bilancio
          AND esito = 'respingimento';

        IF v_respingimenti > 0 THEN
            UPDATE bilanci SET stato = 'respinto'  WHERE id = NEW.id_bilancio;
        ELSE
            UPDATE bilanci SET stato = 'approvato' WHERE id = NEW.id_bilancio;
        END IF;
    END IF;

    CALL sp_aggiorna_indice_affidabilita(NEW.username_revisore);
END$$

-- T3: incrementa nr_bilanci quando si crea un bilancio
CREATE TRIGGER trg_incrementa_nr_bilanci
AFTER INSERT ON bilanci
FOR EACH ROW
BEGIN
    UPDATE aziende
    SET nr_bilanci = nr_bilanci + 1
    WHERE id = NEW.id_azienda;
END$$

-- T4: decrementa nr_bilanci quando si elimina un bilancio
CREATE TRIGGER trg_decrementa_nr_bilanci
AFTER DELETE ON bilanci
FOR EACH ROW
BEGIN
    UPDATE aziende
    SET nr_bilanci = GREATEST(nr_bilanci - 1, 0)
    WHERE id = OLD.id_azienda;
END$$

DELIMITER ;
