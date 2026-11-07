<?php

require_once __DIR__ . '/config.php';

function escapar($valor)
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function datoPost($campo)
{
    return isset($_POST[$campo]) && is_string($_POST[$campo])
        ? $_POST[$campo]
        : '';
}

class Auth
{
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');

            session_name('controlvehicular_session');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => BASE_URL,
                'secure' => !empty($_SERVER['HTTPS'])
                    && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }
    }

    public static function autenticado()
    {
        self::iniciar();

        return isset($_SESSION['user_id']);
    }

    public static function requerirSesion()
    {
        if (!self::autenticado()) {
            header('Location: ' . BASE_URL . 'Views/Auth/login.php');
            exit;
        }

        header('Cache-Control: no-store');
    }

    public static function esAdmin()
    {
        self::iniciar();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Administrador';
    }

    public static function requerirAdmin()
    {
        self::requerirSesion();
        if (!self::esAdmin()) {
            header('Location: ' . BASE_URL . 'Views/Auth/dashboard.php');
            exit;
        }
    }

    public static function soloInvitados()
    {
        if (self::autenticado()) {
            header('Location: ' . BASE_URL . 'Views/Auth/dashboard.php');
            exit;
        }
    }

    public static function token()
    {
        self::iniciar();

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    public static function validarToken()
    {
        self::iniciar();

        return isset($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], datoPost('csrf'));
    }

    public static function acceder($usuario)
    {
        self::iniciar();
        session_regenerate_id(true);

        $_SESSION = [
            'user_id' => (int) $usuario['IdUsuario'],
            'user_name' => $usuario['Nombre'],
            'user_email' => $usuario['Email'],
            'user_role' => $usuario['Rol'],
            'csrf' => bin2hex(random_bytes(32))
        ];
    }

    public static function cerrarSesion()
    {
        self::iniciar();
        $_SESSION = [];

        $cookie = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'secure' => $cookie['secure'],
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_destroy();

        header('Location: ' . BASE_URL . 'Views/Auth/login.php');
        exit;
    }
}
