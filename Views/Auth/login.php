<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../Models/autenticacion.php';

Auth::soloInvitados();

$error = '';
$correo = '';

$registroCorrecto = !empty($_SESSION['registro_correcto']);
unset($_SESSION['registro_correcto']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = strtolower(trim(datoPost('email')));
    $password = datoPost('password');

    if (!Auth::validarToken()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } elseif (
        !Autenticacion::correoValido($correo)
        || $password === ''
        || strlen($password) > 72
        || strpos($password, "\0") !== false
    ) {
        $error = 'Ingresa un correo válido y tu contraseña.';
    } else {
        try {
            $modelo = new Autenticacion();
            $usuario = $modelo->verificar($correo, $password);

            if ($usuario) {
                Auth::acceder($usuario);

                header('Location: ' . BASE_URL . 'Views/Auth/dashboard.php');
                exit;
            }

            $error = 'El correo o la contraseña son incorrectos.';
        } catch (PDOException $ex) {
            error_log('Login: ' . $ex->getMessage());
            $error = 'No se pudo iniciar sesión. Intenta más tarde.';
        }
    }
}

?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Iniciar sesión | Control Vehicular</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link href="<?= BASE_URL ?>Views/assets/auth.css" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/../navbar.php'; ?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card auth-card">
                <div class="card-body p-4 p-lg-5">

                    <h1 class="h3 fw-bold">Iniciar sesión</h1>
                    <p class="text-secondary mb-4">
                        Accede a tu cuenta para continuar.
                    </p>

                    <?php if ($registroCorrecto): ?>
                        <div class="alert alert-success" role="status">
                            Cuenta creada. Ya puedes iniciar sesión.
                        </div>
                    <?php endif; ?>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= escapar($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <input
                            type="hidden"
                            name="csrf"
                            value="<?= escapar(Auth::token()) ?>"
                        >

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Correo electrónico
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control"
                                maxlength="100"
                                autocomplete="email"
                                value="<?= escapar($correo) ?>"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                Contraseña
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <button class="btn btn-primary w-100" type="submit">
                            Ingresar
                        </button>
                    </form>

                    <p class="text-center small text-secondary mt-4 mb-0">
                        ¿No tienes cuenta?
                        <a href="<?= BASE_URL ?>Views/Auth/registro.php">
                            Regístrate aquí
                        </a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../pie.php'; ?>

