<?php
require_once __DIR__ . '/../Config/auth.php';
Auth::start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape($title ?? 'Control Vehicular') ?> | Control Vehicular</title>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
