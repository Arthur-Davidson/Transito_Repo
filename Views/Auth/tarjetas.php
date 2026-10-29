<?php
require_once __DIR__ . '/../../config/auth.php';
Auth::requerirSesion();
$titulo = 'Tarjetas de circulación';
include __DIR__ . '/../cabecera.php';
?>
<main class="container py-5"><div class="card auth-card"><div class="card-body p-4 p-lg-5">
<h1>Tarjetas de circulación</h1>
<p>Esta sección es privada.</p><p class="aviso">vista de tarjetas</p><a href="<?= BASE_URL ?>Views/Auth/dashboard.php">Volver al panel</a>
</div></div></main>
<?php include __DIR__ . '/../pie.php'; ?>
