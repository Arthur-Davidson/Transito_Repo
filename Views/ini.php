<?php
require_once __DIR__ . '/../Config/auth.php';

$title = 'Inicio';
include __DIR__ . '/header.php';
?>
<main class="container py-5">
    <h1>Sistema de Control Vehicular</h1>
    <p>
        Bienvenido al portal de Control Vehicular. Consulta información sobre
        trámites vehiculares, pagos, adeudos y tarjetas de circulación.
    </p>

    <?php if (Auth::isLoggedIn()): ?>
        <p>
            Hola, <strong><?= escape($_SESSION['user_name']) ?></strong>.
            Tu sesión está iniciada. Entra al panel o utiliza el menú para navegar.
        </p>
        <a class="btn btn-primary" href="<?= escape(BASE_URL . 'Views/Auth/dashboard.php') ?>">Ir al panel</a>
    <?php else: ?>
        <p>Visita las secciones públicas o inicia sesión para acceder a las secciones de gestión.</p>
        <a class="btn btn-primary" href="<?= escape(BASE_URL . 'Views/Auth/login.php') ?>">Iniciar sesión</a>
        <a class="btn" href="<?= escape(BASE_URL . 'Views/Auth/register.php') ?>">Crear cuenta</a>
    <?php endif; ?>

    <section>
        <h2>Trámites</h2>
        <ul>
            <li>Registro de propietarios y vehículos.</li>
            <li>Consulta de multas y adeudos vehiculares.</li>
            <li>Consulta de tarjetas de circulación.</li>
            <li>Registro y consulta de pagos vehiculares.</li>
        </ul>
    </section>

    <section>
        <h2>Servicios disponibles</h2>

        <h3>Registro de vehículos</h3>
        <p>Consulta y administra los vehículos registrados.</p>

        <h3>Tarjetas de circulación</h3>
        <p>Consulta la información de las tarjetas de circulación.</p>

        <h3>Pagos y adeudos</h3>
        <p>Consulta los pagos realizados y los adeudos vehiculares.</p>
    </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
