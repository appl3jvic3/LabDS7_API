<?php
// Suppress errors before headers
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'productosdb');
define('DB_USER', 'root');
define('DB_PASS', '');

define('JWT_SECRET', 'TuClaveSuperSecreta123!');
