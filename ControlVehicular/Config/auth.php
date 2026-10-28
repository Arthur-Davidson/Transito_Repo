<?php

require_once __DIR__ . '/config.php';

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function postValue($key)
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? $value : '';
}

class Auth
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn()
    {
        self::start();

        return !empty($_SESSION['user_id']);
    }

    public static function login(array $user)
    {
        self::start();
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['IdUsuario'];
        $_SESSION['user_name'] = $user['Nombre'];
        $_SESSION['user_email'] = $user['Email'];
        $_SESSION['user_role'] = $user['Rol'];
    }

    public static function logout()
    {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . BASE_URL . 'Views/home.php');
        exit;
    }


    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'Views/Auth/login.php');
            exit;
        }
    }


    public static function guestOnly()
    {
        if (self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'Views/Auth/dashboard.php');
            exit;
        }
    }

    public static function csrfToken()
    {
        self::start();

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    public static function validateCsrf()
    {
        self::start();

        $sent = postValue('csrf');

        return $sent !== ''
            && !empty($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $sent);
    }
}
