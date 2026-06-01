<?php
declare(strict_types=1);

define('APP_NAME', getenv('APP_NAME') ?: 'InfoShare Tickets');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/infoShare');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'bd_infoshare_v1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
