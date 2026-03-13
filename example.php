<?php

require_once __DIR__ . '/vendor/autoload.php';

use viavario\rizivclient\RizivClient;

$client = new RizivClient();

// Search by RIZIV number
$result = $client->searchByRegistrationNumber('528532-21');

if ($result !== null) {

    echo "Name:               " . $result->name      . PHP_EOL;
    echo "RIZIV Number:       " . $result->riziv_number . PHP_EOL;
    echo "Profession:         " . $result->profession    . PHP_EOL;
    echo "Contracted:         " . ($result->contracted ? 'Yes' : 'No') . PHP_EOL;
    echo "Qualification:      " . $result->qualification . PHP_EOL;
    echo "Qualification Date: " . $result->qualification_date->format('Y-m-d') . PHP_EOL;
} else {
    echo "No healthcare professional found with the given registration number." . PHP_EOL;
}