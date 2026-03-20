<?php

// Carica l'autoloader di Composer se disponibile (necessario per MongoDB)
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

function getMongoCollection()
{
    static $collection = null;
    static $tried = false;

    if ($tried) {
        return $collection;
    }
    $tried = true;

    // Se la libreria MongoDB non è installata, usare il fallback MySQL
    if (!class_exists('MongoDB\Client')) {
        return null;
    }

    try {
        $client = new MongoDB\Client('mongodb://localhost:27017');
        $collection = $client->esg_balance->events;
    } catch (Throwable $e) {
        error_log('MongoDB non disponibile: ' . $e->getMessage());
        $collection = null;
    }

    return $collection;
}
