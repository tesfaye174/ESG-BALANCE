<?php

// carica l'autoloader di Composer se disponibile (necessario per la libreria MongoDB)
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

function getMongoCollection()
{
    static $collection = null;
    static $tried = false;

    // $tried serve per non riprovare la connessione ad ogni chiamata:
    // se MongoDB non è disponibile, la prima volta setta $tried=true e
    // tutte le chiamate successive ritornano subito null senza aprire connessioni
    if ($tried) {
        return $collection;
    }
    $tried = true;

    // se la libreria non è installata (composer install non eseguito), uso il fallback su MySQL
    if (!class_exists('MongoDB\Client')) {
        return null;
    }

    try {
        $client = new MongoDB\Client('mongodb://localhost:27017');
        // database esg_balance, collection events (viene creata automaticamente se non esiste)
        $collection = $client->esg_balance->events;
    } catch (Throwable $e) {
        // può fallire anche se la libreria c'è ma il server Mongo non è in esecuzione
        error_log('MongoDB non disponibile: ' . $e->getMessage());
        $collection = null;
    }

    return $collection;
}
