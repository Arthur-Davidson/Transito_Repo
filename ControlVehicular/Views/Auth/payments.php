<?php
require_once __DIR__ . '/../../Config/auth.php';

Auth::requireLogin();

$title = 'Pagos';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Pagos</h1>
    <p>Esta sección es privada.</p>
    <p>Contenido pendiente.</p>
    <a href="<?= escape(BASE_URL . 'Views/Auth/dashboard.php') ?>">Volver al panel</a>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
