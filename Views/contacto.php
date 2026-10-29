<?php
require_once __DIR__ . '/../config/auth.php';
Auth::iniciar();
$titulo = 'Contacto';
include __DIR__ . '/cabecera.php';
?>
<main class="container py-5"><div class="card auth-card"><div class="card-body p-4 p-lg-5">
<h1>Contacto</h1>
<p>Esta sección es pública y está disponible con o sin sesión.</p><p class="aviso">Aquí podrán agregarse los datos de contacto del proyecto.</p>
</div></div></main>
<?php include __DIR__ . '/pie.php'; ?>
