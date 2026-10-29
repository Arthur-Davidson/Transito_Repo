<?php

require_once __DIR__ . '/../config/database.php';

class Autenticacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function correoValido($correo)
    {
        return strlen($correo) <= 100
            && filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validarRegistro(
        $nombre,
        $correo,
        $password,
        $confirmacion
    ) {
        if (
            $nombre === ''
            || $correo === ''
            || $password === ''
            || $confirmacion === ''
        ) {
            return 'Completa todos los campos.';
        }

        // Letras Unicode, acentos y separadores entre palabras.
        $patron = "/\A[\p{L}\p{M}]+(?:[ .’'\-][\p{L}\p{M}]+)*\z/u";

        if (
            !preg_match($patron, $nombre)
            || mb_strlen($nombre, 'UTF-8') > 100
        ) {
            return 'Ingresa un nombre válido de hasta 100 caracteres.';
        }

        if (!self::correoValido($correo)) {
            return 'Ingresa un correo válido de hasta 100 caracteres.';
        }

        if (
            !mb_check_encoding($password, 'UTF-8')
            || strpos($password, "\0") !== false
            || mb_strlen($password, 'UTF-8') < 8
            || strlen($password) > 72
        ) {
            return 'La contraseña necesita al menos 8 caracteres '
                . 'y no debe superar 72 bytes.';
        }

        if ($password !== $confirmacion) {
            return 'Las contraseñas no coinciden.';
        }

        return '';
    }

    public function registrar(
        $nombre,
        $correo,
        $password
    ) {
        if (
            !mb_check_encoding($password, 'UTF-8')
            || strpos($password, "\0") !== false
            || mb_strlen($password, 'UTF-8') < 8
            || strlen($password) > 72
        ) {
            throw new InvalidArgumentException(
                'La contraseña necesita al menos 8 caracteres '
                . 'y no debe superar 72 bytes.'
            );
        }

        $consulta = $this->db->prepare(
            'SELECT IdUsuario FROM Usuarios
             WHERE Email = :email LIMIT 1'
        );

        $consulta->execute(['email' => $correo]);

        if ($consulta->fetch()) {
            return false;
        }

        $consulta = $this->db->prepare(
            'INSERT INTO Usuarios (Nombre, Email, Password, Rol)
             VALUES (:nombre, :email, :password, :rol)'
        );

        try {
            $consulta->execute([
                'nombre' => $nombre,
                'email' => $correo,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'rol' => 'Usuario'
            ]);

            return true;
        } catch (PDOException $error) {
            // Correo duplicado: protegido también por índice UNIQUE.
            if (($error->errorInfo[1] ?? null) === 1062) {
                return false;
            }

            throw $error;
        }
    }

    public function verificar($correo, $password)
    {
        $consulta = $this->db->prepare(
            'SELECT IdUsuario, Nombre, Email, Password, Rol
             FROM Usuarios WHERE Email = :email LIMIT 2'
        );

        $consulta->execute(['email' => $correo]);
        $usuarios = $consulta->fetchAll();

        if (count($usuarios) !== 1) {
            return false;
        }

        $usuario = $usuarios[0];

        if (!password_verify($password, $usuario['Password'])) {
            return false;
        }

        return $usuario;
    }
}