<?php

require_once __DIR__ . '/../config/database.php';

// chiama una stored procedure e restituisce i risultati come array associativo
// uso nextRowset perché MySQL può restituire più result set da una singola SP
function callSP(string $sp_name, array $params = []): array
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $results = $stmt->fetchAll();
    // alcune SP restituiscono prima un result set vuoto (es. OK packet), scorro finché non trovo righe
    while (empty($results) && $stmt->nextRowset()) {
        $results = $stmt->fetchAll();
    }
    $stmt->closeCursor(); // chiudo il cursore per liberare la connessione prima di altre query
    return $results;
}

// versione senza risultati — per SP che fanno solo INSERT/UPDATE/DELETE
function execSP(string $sp_name, array $params = []): void
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stmt->closeCursor();
}

// SELECT generica con prepared statement — uso quando non ho una SP apposita
function query(string $sql, array $params = []): array
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// come query() ma restituisce solo la prima riga — utile per lookup per ID o username
function queryOne(string $sql, array $params = []): ?array
{
    $rows = query($sql, $params);
    return $rows[0] ?? null;
}
