<?php
require_once __DIR__ . '/../config/auth.php';
Auth::iniciar();
$actual = basename($_SERVER['SCRIPT_NAME'] ?? '');
function enlaceMenu($archivo, $texto, $privado = false) {
    global $actual;
    $ruta = BASE_URL . 'Views/' . ($privado ? 'Auth/' : '') . $archivo;
    $activo = $actual === basename($archivo);
    echo '<a class="nav-link' . ($activo ? ' active' : '') . '" href="' . escapar($ruta) . '"' . ($activo ? ' aria-current="page"' : '') . '>' . escapar($texto) . '</a>';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
<div class="container"><a class="navbar-brand brand-mark" href="<?= BASE_URL ?>Views/home.php">Control Vehicular</a>
<button class="navbar-toggler" type="button" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Mostrar navegación" id="abrirMenu"><span aria-hidden="true">☰</span></button>
<div class="collapse navbar-collapse" id="menuPrincipal">
<div class="navbar-nav me-auto">
<?php enlaceMenu('home.php', 'Inicio'); enlaceMenu('consulta.php', 'Consulta'); ?>
<?php if (Auth::autenticado()): ?>
<?php enlaceMenu('dashboard.php', 'Panel', true); ?>
<details class="gestion"><summary>Gestión</summary><div class="gestion-menu">
<?php foreach (['propietarios.php'=>'Propietarios', 'vehiculos.php'=>'Vehículos', 'tarjetas.php'=>'Tarjetas', 'pagos.php'=>'Pagos'] as $archivo=>$texto) enlaceMenu($archivo, $texto, true); ?>
</div></details>
<?php endif; ?>
</div><div class="navbar-nav align-items-lg-center gap-lg-3">
<?php if (Auth::autenticado()): ?>
<span class="navbar-text">Hola, <strong><?= escapar($_SESSION['user_name']) ?></strong></span>
<form action="<?= BASE_URL ?>Views/Auth/logout.php" method="post">
<input type="hidden" name="csrf" value="<?= escapar(Auth::token()) ?>"><button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button></form>
<?php else: ?>
<?php enlaceMenu('Auth/login.php', 'Iniciar sesión'); ?>
<a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>Views/Auth/registro.php">Registrarse</a>
<?php endif; ?>
</div></div></div></nav>
