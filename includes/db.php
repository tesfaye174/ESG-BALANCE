<?php

require_once __DIR__ . '/../config/database.php';

function callSP(string $sp_name, array $params = []): array
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Le SP con INSERT + SELECT restituiscono più resultset:
    // il primo può essere vuoto (dall'INSERT), il secondo ha i dati.
    // Scorriamo finché troviamo un resultset non vuoto.
    $results = $stmt->fetchAll();
    while (empty($results) && $stmt->nextRowset()) {
        $results = $stmt->fetchAll();
    }
    $stmt->closeCursor();
    return $results;
}

function execSP(string $sp_name, array $params = []): void
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stmt->closeCursor();
}

function query(string $sql, array $params = []): array
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function queryOne(string $sql, array $params = []): ?array
{
    $rows = query($sql, $params);
    return $rows[0] ?? null;
}
