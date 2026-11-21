<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar($titulo ?? 'Control Vehicular') ?> | Control Vehicular</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>Views/assets/auth.css?v=<?= file_exists(__DIR__ . '/assets/auth.css') ? filemtime(__DIR__ . '/assets/auth.css') : '1.1' ?>" rel="stylesheet">
    </head>

    <body>
        <?php include __DIR__ . '/navbar.php'; ?>