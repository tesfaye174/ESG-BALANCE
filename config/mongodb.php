<?php

// connessione mongodb
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

    if (!class_exists('MongoDB\Client')) {
        return null;
    }

    try {
        $uri = getenv('MONGO_URI') ?: 'mongodb://localhost:27017';
        $client = new MongoDB\Client($uri);
        $collection = $client->esg_balance->events;
    } catch (Throwable $e) {
        error_log('MongoDB non disponibile: ' . $e->getMessage());
        $collection = null;
    }

    return $collection;
}
