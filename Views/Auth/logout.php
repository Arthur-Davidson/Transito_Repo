<?php
require_once __DIR__ . '/../../Config/auth.php';

Auth::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Usa el botón Cerrar sesión del menú.');
}

if (!Auth::validateCsrf()) {
    http_response_code(403);
    exit('Formulario expirado. Regresa y recarga la página.');
}

Auth::logout();
