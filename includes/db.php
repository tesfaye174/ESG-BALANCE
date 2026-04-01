<?php

require_once __DIR__ . '/../config/database.php';

// esegue la SP e ritorna le righe
function callSP(string $sp_name, array $params = []): array
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $results = $stmt->fetchAll();
    // alcune SP mandano un result set vuoto prima dei dati
    while (empty($results) && $stmt->nextRowset()) {
        $results = $stmt->fetchAll();
    }
    $stmt->closeCursor();
    return $results;
}

// versione senza risultati
function execSP(string $sp_name, array $params = []): void
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stmt->closeCursor();
}

// SELECT diretta quando non ho una SP
function query(string $sql, array $params = []): array
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// come query() ma ritorna solo la prima riga
function queryOne(string $sql, array $params = []): ?array
{
    $rows = query($sql, $params);
    return $rows[0] ?? null;
}