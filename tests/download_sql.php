<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

require_once 'config.php';

use MysqlToFirestore\MysqlHelper;

MysqlHelper::download_sql($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD, $MYSQL_DATABASE);