<?php
require_once __DIR__ . '/../../Config/auth.php';
require_once __DIR__ . '/../../Models/authentication.php';

Auth::guestOnly();

$error = '';
$email = '';

$registrationSuccess = !empty($_SESSION['registration_success']);
unset($_SESSION['registration_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim(postValue('email')));
    $password = postValue('password');

    if (!Auth::validateCsrf()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } elseif (
        !Authentication::isValidEmail($email)
        || $password === ''
        || strlen($password) > 72
        || strpos($password, "\0") !== false
    ) {
        $error = 'Ingresa un correo válido y tu contraseña.';
    } else {
        try {
            $model = new Authentication();
            $user = $model->verify($email, $password);

            if ($user) {
                Auth::login($user);

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

$title = 'Iniciar sesión';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Iniciar sesión</h1>
    <p>Accede a tu cuenta para continuar.</p>

    <?php if ($registrationSuccess): ?>
        <p role="status">Cuenta creada. Ya puedes iniciar sesión.</p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p role="alert"><?= escape($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= escape(Auth::csrfToken()) ?>">

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input
                id="email"
                name="email"
                type="email"
                class="form-control"
                maxlength="100"
                autocomplete="email"
                value="<?= escape($email) ?>"
                required
            >
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <input
                id="password"
                name="password"
                type="password"
                class="form-control"
                autocomplete="current-password"
                required
            >
        </div>

        <button class="btn btn-primary" type="submit">Ingresar</button>
    </form>

    <p>
        ¿No tienes cuenta?
        <a href="<?= escape(BASE_URL . 'Views/Auth/register.php') ?>">Regístrate aquí</a>
    </p>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
