<?php

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/card.php';

class Vehiculo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar()
    {
        return $this->db->query(
            'SELECT v.IdVehiculo, v.Matricula, v.NumeroSerie, v.Marca,
                    v.Modelo, v.Anio, v.Color, p.Nombre, p.Apellido
             FROM Vehiculos v
             INNER JOIN Propietarios p ON p.IdPropietario = v.IdPropietario
             ORDER BY v.IdVehiculo DESC'
        )->fetchAll();
    }

    public function propietarios()
    {
        return $this->db->query(
            'SELECT IdPropietario, Nombre, Apellido
             FROM Propietarios ORDER BY Apellido, Nombre'
        )->fetchAll();
    }

    /**
     * Registra un vehículo y genera automáticamente su tarjeta de circulación
     * con vigencia de un año a partir de hoy.
     */
    public function crear(array $datos)
    {
        $consulta = $this->db->prepare(
            'INSERT INTO Vehiculos
                (Matricula, NumeroSerie, Marca, Modelo, Anio, Color, IdPropietario)
             VALUES
                (:matricula, :numero_serie, :marca, :modelo, :anio, :color, :propietario)'
        );

        $consulta->execute($datos);

        $idVehiculo = (int) $this->db->lastInsertId();

        // Generar tarjeta de circulación automáticamente
        (new TarjetaCirculacion())->crearParaVehiculo($idVehiculo);
    }
}