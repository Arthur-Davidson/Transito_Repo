<?php
require_once __DIR__ . '/../config/auth.php';
Auth::iniciar();
$titulo = 'Consulta';
include __DIR__ . '/cabecera.php';
?>
<main class="container py-5"><div class="card auth-card"><div class="card-body p-4 p-lg-5">
<h1>Consulta</h1>
<p>aqui va lo de consulta</p>
</div></div></main>
<?php include __DIR__ . '/pie.php'; ?>
