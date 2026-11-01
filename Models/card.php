<?php

require_once __DIR__ . '/../Config/database.php';

class TarjetaCirculacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar()
    {
        return $this->db->query(
            'SELECT t.IdTarjeta, t.NumeroFolio, t.FechaExpedicion,
                    t.FechaVencimiento, t.Estatus, v.Matricula,
                    p.Nombre, p.Apellido
             FROM TarjetasCirculacion t
             INNER JOIN Vehiculos v ON v.IdVehiculo = t.IdVehiculo
             INNER JOIN Propietarios p ON p.IdPropietario = t.IdPropietario
             ORDER BY t.IdTarjeta DESC'
        )->fetchAll();
    }

    public function opciones()
    {
        return $this->db->query(
            'SELECT v.IdVehiculo, v.Matricula, v.Marca, v.Modelo, v.IdPropietario,
                    p.Nombre, p.Apellido
             FROM Vehiculos v
             INNER JOIN Propietarios p ON p.IdPropietario = v.IdPropietario
             ORDER BY v.Matricula'
        )->fetchAll();
    }

    public function crear(array $datos)
    {
        $consulta = $this->db->prepare(
            'INSERT INTO TarjetasCirculacion
                (NumeroFolio, FechaExpedicion, FechaVencimiento, Estatus,
                 IdVehiculo, IdPropietario)
             SELECT :folio, :expedicion, :vencimiento, :estatus,
                    v.IdVehiculo, v.IdPropietario
             FROM Vehiculos v WHERE v.IdVehiculo = :vehiculo'
        );

        $consulta->execute($datos);

        return $consulta->rowCount() === 1;
    }
}