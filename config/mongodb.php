<?php

define('MONGO_URI', 'mongodb://localhost:27017');
define('MONGO_DB', 'esg_balance');
define('MONGO_COLLECTION', 'events');

// singleton anche qui come per MySQL
function getMongoCollection()
{
    static $collection = null;

    if ($collection === null) {
        $client = new MongoDB\Client(MONGO_URI);
        $collection = $client->selectCollection(MONGO_DB, MONGO_COLLECTION);
    }

    return $collection;
}
