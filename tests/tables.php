<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

require_once 'config.php';

use MysqlToFirestore\MysqlHelper;

print_r(MysqlHelper::tables($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD, $MYSQL_DATABASE));

