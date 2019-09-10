<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use MysqlToFirestore\MigrateMysqlToFirestore;

# Explicitly use service account credentials by specifying the private key file.
$config = [
    'keyFilePath' => 'firestore-demo-ruby-e1cbef86b597.json',
    'projectId' => 'firestore-demo-ruby',
];