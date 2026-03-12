<?php

require_once __DIR__ . '/../config/database.php';

function callSP(string $sp_name, array $params = []): array
{
    $pdo = getDBConnection();
    $placeholders = implode(',', array_fill(0, count($params), '?'));
    $sql = "CALL {$sp_name}({$placeholders})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    $stmt->closeCursor(); // serve dopo CALL senno' da errore alla query dopo
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
