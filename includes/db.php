<?php

require_once __DIR__ . '/../config/database.php';

// callSP: chiama una stored procedure e restituisce il risultato
// da usare quando la SP fa una SELECT alla fine (es. sp_login, sp_crea_bilancio)
function callSP(string $sp_name, array $params = []): array
{
    $pdo = getDBConnection();
    // costruisco i placeholder '?,?,?' in base al numero di parametri
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // alcune SP con INSERT + SELECT restituiscono più resultset:
    // il primo è vuoto (viene dall'INSERT), il secondo contiene i dati veri.
    // scorro con nextRowset() finché trovo un resultset non vuoto.
    $results = $stmt->fetchAll();
    while (empty($results) && $stmt->nextRowset()) {
        $results = $stmt->fetchAll();
    }
    $stmt->closeCursor();
    return $results;
}

// execSP: versione semplificata per SP che non restituiscono dati (INSERT/UPDATE/DELETE puri)
function execSP(string $sp_name, array $params = []): void
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stmt->closeCursor();
}

// query() e queryOne() sono wrapper corti per le query dirette (senza SP)
function query(string $sql, array $params = []): array
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// restituisce solo la prima riga oppure null se non trovato
function queryOne(string $sql, array $params = []): ?array
{
    $rows = query($sql, $params);
    return $rows[0] ?? null;
}
