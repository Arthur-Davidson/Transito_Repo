
<?php
require_once __DIR__ . '/../Config/auth.php';

Auth::iniciar();

$titulo = 'Control Vehicular';

include __DIR__ . '/header.php';
?>

<main class="container py-5">
    <div class="card auth-card">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Control Vehicular</h1>

                    <p>Bienvenido al portal de Control Vehicular.</p>

                    <?php if (Auth::autenticado()): ?>
                        <p>
                            Hola, <strong><?= escapar($_SESSION['user_name']) ?></strong>.
                            Tu sesión está iniciada. Entra al panel o utiliza Gestión para navegar.
                        </p>
                        <a class="btn btn-primary" href="<?= BASE_URL ?>Views/Auth/dashboard.php">
                            Ir al panel
                        </a>

                    <?php else: ?>
                        <p>
                            Visita las secciones públicas o inicia sesión para acceder a las secciones de gestión.
                        </p>
                        <div class="acciones">
                            <a class="btn btn-primary" href="<?= BASE_URL ?>Views/Auth/login.php">
                                Iniciar sesión
                            </a>
                            <a href="<?= BASE_URL ?>Views/Auth/register.php" class="btn">
                                Crear cuenta
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 text-center mt-4 mt-md-0">
                    <img src="<?= BASE_URL ?>Img/img_01.png" alt="Vehículo" class="img-fluid" style="max-height: 300px;">
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
