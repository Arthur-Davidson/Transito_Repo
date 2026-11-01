<?php
require_once __DIR__ . '/../../Config/auth.php';
Auth::requerirSesion();
$titulo = 'Panel de gestión';
include __DIR__ . '/../header.php';
?>
<main class="container py-5"><div class="card auth-card"><div class="card-body p-4 p-lg-5">
<h1>Panel de gestión</h1>
<p>Bienvenido, <strong><?= escapar($_SESSION['user_name']) ?></strong>.</p><p>Correo: <?= escapar($_SESSION['user_email']) ?></p>
<p>Has iniciado sesión. Desde aquí puedes acceder a las secciones de gestión.</p><div class="acciones">
<?php foreach (['owners'=>'Propietarios','vehicles'=>'Vehículos','cards'=>'Tarjetas','payments'=>'Pagos'] as $ruta=>$texto): ?>
<a class="btn btn-primary" href="<?= BASE_URL ?>Views/Auth/<?= escapar($ruta) ?>.php"><?= escapar($texto) ?></a>
<?php endforeach; ?></div>
</div></div></main>
<?php include __DIR__ . '/../footer.php'; ?>
