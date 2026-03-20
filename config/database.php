<?php

// leggo i parametri da variabili d'ambiente se disponibili, altrimenti uso i default
// così in locale funziona senza configurare nulla, ma su un server si possono
// sovrascrivere senza toccare il codice
define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'esg_balance');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// uso una variabile statica così non riapro la connessione ogni volta
// (pattern singleton: la connessione viene creata solo al primo utilizzo)
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
