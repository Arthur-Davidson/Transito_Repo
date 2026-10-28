<?php
require_once __DIR__ . '/../Config/auth.php';
Auth::start();

$currentFile = basename($_SERVER['SCRIPT_NAME'] ?? '');

function menuLink($path, $label)
{
    global $currentFile;

    $isActive = $currentFile === basename($path);

    echo '<a class="nav-link' . ($isActive ? ' active' : '') . '"'
        . ' href="' . escape(BASE_URL . 'Views/' . $path) . '"'
        . ($isActive ? ' aria-current="page"' : '') . '>'
        . escape($label) . '</a>';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= escape(BASE_URL . 'Views/ini.php') ?>">Control Vehicular</a>

        <div class="navbar-nav me-auto">
            <?php
            menuLink('ini.php', 'Inicio');
            menuLink('consulta.php', 'Consulta');
            menuLink('contacto.php', 'Contacto');
            ?>

            <?php if (Auth::isLoggedIn()): ?>
                <?php
                menuLink('Auth/dashboard.php', 'Panel');
                menuLink('Auth/owners.php', 'Propietarios');
                menuLink('Auth/vehicles.php', 'Vehículos');
                menuLink('Auth/cards.php', 'Tarjetas');
                menuLink('Auth/payments.php', 'Pagos');
                ?>
            <?php endif; ?>
        </div>

        <div class="navbar-nav align-items-lg-center gap-lg-3">
            <?php if (Auth::isLoggedIn()): ?>
                <span class="navbar-text">Hola, <strong><?= escape($_SESSION['user_name']) ?></strong></span>

                <form action="<?= escape(BASE_URL . 'Views/Auth/logout.php') ?>" method="post">
                    <input type="hidden" name="csrf" value="<?= escape(Auth::csrfToken()) ?>">
                    <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <?php
                menuLink('Auth/login.php', 'Iniciar sesión');
                menuLink('Auth/register.php', 'Registrarse');
                ?>
            <?php endif; ?>
        </div>
    </div>
</nav>
