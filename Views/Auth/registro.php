<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../Models/autenticacion.php';

Auth::soloInvitados();

$error = '';
$nombre = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(datoPost('nombre'));
    $correo = strtolower(trim(datoPost('email')));
    $password = datoPost('password');
    $confirmacion = datoPost('confirmacion');

    if (!Auth::validarToken()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } else {
        $error = Autenticacion::validarRegistro(
            $nombre,
            $correo,
            $password,
            $confirmacion
        );

        if ($error === '') {
            try {
                $modelo = new Autenticacion();

                if ($modelo->registrar(
                    $nombre,
                    $correo,
                    $password
                )) {
                    $_SESSION['registro_correcto'] = true;

                    header('Location: ' . BASE_URL . 'Views/Auth/login.php');
                    exit;
                }

                $error = 'Este correo ya está registrado.';
            } catch (PDOException $ex) {
                error_log('Registro: ' . $ex->getMessage());
                $error = 'No se pudo crear la cuenta. Intenta más tarde.';
            }
        }
    }
}

?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registro | Control Vehicular</title>

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
        <div class="col-12 col-md-9 col-lg-6">
            <div class="card auth-card">
                <div class="card-body p-4 p-lg-5">

                    <h1 class="h3 fw-bold">Crear cuenta</h1>
                    <p class="text-secondary mb-4">
                        Completa tus datos para registrarte.
                    </p>

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
                            <label for="nombre" class="form-label">
                                Nombre
                            </label>

                            <input
                                id="nombre"
                                name="nombre"
                                type="text"
                                class="form-control"
                                maxlength="100"
                                autocomplete="name"
                                value="<?= escapar($nombre) ?>"
                                required
                            >
                        </div>

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

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Contraseña
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                aria-describedby="ayudaPassword"
                                required
                            >

                            <div id="ayudaPassword" class="form-text">
                                Crea una contraseña de al menos 8 caracteres.
                                Puedes usar espacios, acentos y símbolos.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmacion" class="form-label">
                                Confirmar contraseña
                            </label>

                            <input
                                id="confirmacion"
                                name="confirmacion"
                                type="password"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <button class="btn btn-primary w-100" type="submit">
                            Crear cuenta
                        </button>
                    </form>

                    <p class="text-center small text-secondary mt-4 mb-0">
                        ¿Ya tienes cuenta?
                        <a href="<?= BASE_URL ?>Views/Auth/login.php">
                            Inicia sesión
                        </a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</main>

<script src="<?= BASE_URL ?>Views/assets/navegacion.js"></script>
</body>
</html>