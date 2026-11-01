<?php

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'ControlVehicular');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

$appBasePath = getenv('APP_BASE_URL');
if ($appBasePath === false || $appBasePath === '') {
    $scriptPath = str_replace(
        '\\',
        '/',
        $_SERVER['SCRIPT_NAME'] ?? '/Transito_Repo/index.php'
    );
    $viewsPosition = strpos($scriptPath, '/Views/');
    $appBasePath = $viewsPosition === false
        ? dirname($scriptPath)
        : substr($scriptPath, 0, $viewsPosition);
}

$appBasePath = str_replace('\\', '/', $appBasePath);
if ($appBasePath !== '' && $appBasePath[0] !== '/') {
    $appBasePath = '/' . $appBasePath;
}

define('BASE_URL', rtrim($appBasePath, '/') . '/');
