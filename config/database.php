<?php

// i parametri si possono sovrascrivere via variabili d'ambiente, così non tocco il codice in produzione
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'esg_balance');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// uso una variabile statica così non riapro la connessione ogni volta

function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // lancia eccezioni invece di restituire false
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // restituisce array associativi
            PDO::ATTR_EMULATE_PREPARES   => false,                   // prepared statement reali, non simulati
        ]);
    }

    return $pdo;
}