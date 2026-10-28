<?php
require_once __DIR__ . '/../../Config/auth.php';
require_once __DIR__ . '/../../Models/authentication.php';

Auth::guestOnly();

$error = '';
$name = '';
$email = '';
$role = Authentication::ROLE_USER;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(postValue('name'));
    $email = strtolower(trim(postValue('email')));
    $password = postValue('password');
    $passwordConfirm = postValue('password_confirm');
    $role = postValue('role');
    $inviteCode = postValue('invite_code');

    if (!Auth::validateCsrf()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } else {
        $error = Authentication::validateRegistration(
            $name,
            $email,
            $password,
            $passwordConfirm,
            $role,
            $inviteCode
        );

        if ($error === '') {
            try {
                $model = new Authentication();

                if ($model->register($name, $email, $password, $role, $inviteCode)) {
                    $_SESSION['registration_success'] = true;

                    header('Location: ' . BASE_URL . 'Views/Auth/login.php');
                    exit;
                }

                $error = 'Este correo ya está registrado.';
            } catch (PDOException $ex) {
                error_log('Register: ' . $ex->getMessage());
                $error = 'No se pudo crear la cuenta. Intenta más tarde.';
            }
        }
    }
}

$title = 'Registro';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Crear cuenta</h1>
    <p>Completa tus datos para registrarte.</p>

    <?php if ($error !== ''): ?>
        <p role="alert"><?= escape($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= escape(Auth::csrfToken()) ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input
                id="name"
                name="name"
                type="text"
                class="form-control"
                maxlength="100"
                autocomplete="name"
                value="<?= escape($name) ?>"
                required
            >
        </div>

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

        <div class="mb-3">
            <label for="role" class="form-label">Tipo de cuenta</label>
            <select id="role" name="role" class="form-select" required>
                <option
                    value="<?= escape(Authentication::ROLE_USER) ?>"
                    <?= $role === Authentication::ROLE_USER ? 'selected' : '' ?>
                >Usuario</option>
                <option
                    value="<?= escape(Authentication::ROLE_ADMIN) ?>"
                    <?= $role === Authentication::ROLE_ADMIN ? 'selected' : '' ?>
                >Administrador</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="invite_code" class="form-label">
                Código de invitación de administrador
            </label>
            <input
                id="invite_code"
                name="invite_code"
                type="password"
                class="form-control"
                autocomplete="off"
                aria-describedby="inviteCodeHelp"
            >
            <div id="inviteCodeHelp" class="form-text">
                Solo se requiere para crear una cuenta de Administrador.
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input
                id="password"
                name="password"
                type="password"
                class="form-control"
                minlength="8"
                autocomplete="new-password"
                aria-describedby="passwordHelp"
                required
            >
            <div id="passwordHelp" class="form-text">
                Crea una contraseña de al menos 8 caracteres.
                Puedes usar espacios, acentos y símbolos.
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirm" class="form-label">Confirmar contraseña</label>
            <input
                id="password_confirm"
                name="password_confirm"
                type="password"
                class="form-control"
                minlength="8"
                autocomplete="new-password"
                required
            >
        </div>

        <button class="btn btn-primary" type="submit">Crear cuenta</button>
    </form>

    <p>
        ¿Ya tienes cuenta?
        <a href="<?= escape(BASE_URL . 'Views/Auth/login.php') ?>">Inicia sesión</a>
    </p>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
