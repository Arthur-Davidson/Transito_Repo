<?php

require_once __DIR__ . '/../Config/database.php';

class Propietario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar()
    {
        return $this->db->query(
            'SELECT IdPropietario, Nombre, Apellido, RFC, Telefono, Direccion
             FROM Propietarios ORDER BY IdPropietario DESC'
        )->fetchAll();
    }

    public function crear(array $datos)
    {
        $consulta = $this->db->prepare(
            'INSERT INTO Propietarios
                (Nombre, Apellido, RFC, Telefono, Direccion)
             VALUES (:nombre, :apellido, :rfc, :telefono, :direccion)'
        );

        $consulta->execute($datos);
    }
}