<?php

require_once __DIR__ . '/../Config/database.php';

class Pago
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function listar()
    {
        return $this->db->query(
            'SELECT pa.IdPago, pa.FolioPago, pa.Monto, pa.FechaPago,
                    pa.Concepto, pa.Estatus, v.Matricula,
                    p.Nombre, p.Apellido
             FROM Pagos pa
             INNER JOIN Vehiculos v ON v.IdVehiculo = pa.IdVehiculo
             INNER JOIN Propietarios p ON p.IdPropietario = pa.IdPropietario
             ORDER BY pa.IdPago DESC'
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
            'INSERT INTO Pagos
                (FolioPago, Monto, FechaPago, Concepto, Estatus,
                 IdPropietario, IdVehiculo)
             SELECT :folio, :monto, :fecha, :concepto, :estatus,
                    v.IdPropietario, v.IdVehiculo
             FROM Vehiculos v WHERE v.IdVehiculo = :vehiculo'
        );

        $consulta->execute($datos);

        return $consulta->rowCount() === 1;
    }
}