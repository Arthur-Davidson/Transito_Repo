<?php
require_once __DIR__ . '/../../Config/auth.php';

Auth::requireLogin();

$title = 'Panel de usuario';
include __DIR__ . '/../header.php';

$sections = [
    'owners' => 'Propietarios',
    'vehicles' => 'Vehículos',
    'cards' => 'Tarjetas',
    'payments' => 'Pagos',
];
?>
<main class="container py-5">
    <h1>Panel de usuario</h1>
    <p>Bienvenido, <strong><?= escape($_SESSION['user_name']) ?></strong>.</p>
    <p>Correo: <?= escape($_SESSION['user_email']) ?></p>
    <p>Has accedido a una página privada.</p>

    <div>
        <?php foreach ($sections as $page => $label): ?>
            <a class="btn btn-primary" href="<?= escape(BASE_URL . 'Views/Auth/' . $page . '.php') ?>"><?= escape($label) ?></a>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
