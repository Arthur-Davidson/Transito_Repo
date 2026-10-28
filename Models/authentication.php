<?php

require_once __DIR__ . '/../Config/database.php';

class Authentication
{
    const ROLE_USER = 'Usuario';
    const ROLE_ADMIN = 'Administrador';

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function isValidEmail($email)
    {
        return strlen($email) <= 100
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private static function isValidRole($role)
    {
        return in_array($role, [self::ROLE_USER, self::ROLE_ADMIN], true);
    }

    private static function isValidPassword($password)
    {
        return mb_check_encoding($password, 'UTF-8')
            && strpos($password, "\0") === false
            && mb_strlen($password, 'UTF-8') >= 8
            && strlen($password) <= 72;
    }

    private static function isValidInviteCode($code)
    {
        return ADMIN_INVITE_CODE !== ''
            && $code !== ''
            && hash_equals(ADMIN_INVITE_CODE, $code);
    }

 
    public static function validateRegistration(
        $name,
        $email,
        $password,
        $passwordConfirm,
        $role = self::ROLE_USER,
        $inviteCode = ''
    ) {
        if (
            $name === ''
            || $email === ''
            || $password === ''
            || $passwordConfirm === ''
        ) {
            return 'Completa todos los campos.';
        }

        if (!self::isValidRole($role)) {
            return 'Selecciona un rol válido.';
        }

        // Unicode letters, accents and separators between words.
        $pattern = "/\A[\p{L}\p{M}]+(?:[ .’'\-][\p{L}\p{M}]+)*\z/u";

        if (
            !preg_match($pattern, $name)
            || mb_strlen($name, 'UTF-8') > 100
        ) {
            return 'Ingresa un nombre válido de hasta 100 caracteres.';
        }

        if (!self::isValidEmail($email)) {
            return 'Ingresa un correo válido de hasta 100 caracteres.';
        }

        if (!self::isValidPassword($password)) {
            return 'La contraseña necesita al menos 8 caracteres '
                . 'y no debe superar 72 bytes.';
        }

        if ($password !== $passwordConfirm) {
            return 'Las contraseñas no coinciden.';
        }

        if ($role === self::ROLE_ADMIN && !self::isValidInviteCode($inviteCode)) {
            return 'El código de invitación de administrador es inválido '
                . 'o no está configurado.';
        }

        return '';
    }

 
    public function register(
        $name,
        $email,
        $password,
        $role = self::ROLE_USER,
        $inviteCode = ''
    ) {
        if (!self::isValidRole($role)) {
            throw new InvalidArgumentException('El rol seleccionado no es válido.');
        }

        if (!self::isValidPassword($password)) {
            throw new InvalidArgumentException(
                'La contraseña necesita al menos 8 caracteres '
                . 'y no debe superar 72 bytes.'
            );
        }

        if ($role === self::ROLE_ADMIN && !self::isValidInviteCode($inviteCode)) {
            throw new InvalidArgumentException(
                'El código de invitación de administrador es inválido '
                . 'o no está configurado.'
            );
        }

        $query = $this->db->prepare(
            'SELECT IdUsuario FROM Usuarios
             WHERE Email = :email LIMIT 1'
        );

        $query->execute(['email' => $email]);

        if ($query->fetch()) {
            return false;
        }

        $query = $this->db->prepare(
            'INSERT INTO Usuarios (Nombre, Email, Password, Rol)
             VALUES (:name, :email, :password, :role)'
        );

        try {
            $query->execute([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => $role
            ]);

            return true;
        } catch (PDOException $error) {
            if (($error->errorInfo[1] ?? null) === 1062) {
                return false;
            }

            throw $error;
        }
    }


    public function verify($email, $password)
    {
        $query = $this->db->prepare(
            'SELECT IdUsuario, Nombre, Email, Password, Rol
             FROM Usuarios WHERE Email = :email LIMIT 2'
        );

        $query->execute(['email' => $email]);
        $users = $query->fetchAll();

        if (count($users) !== 1) {
            return false;
        }

        $user = $users[0];

        if (!password_verify($password, $user['Password'])) {
            return false;
        }

        return $user;
    }
}
