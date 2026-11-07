<?php

require_once __DIR__ . '/../Config/database.php';

class TarjetaCirculacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Crea automáticamente una tarjeta al registrar un vehículo.
     * Fecha de expedición = hoy, vencimiento = hoy + 1 año.
     */
    public function crearParaVehiculo(int $idVehiculo): bool
    {
        $hoy       = date('Y-m-d');
        $vence     = date('Y-m-d', strtotime('+1 year'));
        $folio     = 'TC-' . strtoupper(bin2hex(random_bytes(5)));

        $consulta = $this->db->prepare(
            'INSERT INTO TarjetasCirculacion
                (NumeroFolio, FechaExpedicion, FechaVencimiento, Estatus,
                 IdVehiculo, IdPropietario)
             SELECT :folio, :expedicion, :vencimiento, :estatus,
                    v.IdVehiculo, v.IdPropietario
             FROM Vehiculos v WHERE v.IdVehiculo = :vehiculo'
        );

        $consulta->execute([
            'folio'      => $folio,
            'expedicion' => $hoy,
            'vencimiento'=> $vence,
            'estatus'    => 'Vigente',
            'vehiculo'   => $idVehiculo,
        ]);

        return $consulta->rowCount() === 1;
    }

    /**
     * Busca la tarjeta de circulación vigente (más reciente) por matrícula.
     */
    public function buscarPorMatricula(string $matricula): ?array
    {
        $consulta = $this->db->prepare(
            'SELECT t.IdTarjeta, t.NumeroFolio, t.FechaExpedicion,
                    t.FechaVencimiento, t.Estatus,
                    v.Matricula, v.Marca, v.Modelo, v.Anio, v.Color,
                    p.Nombre, p.Apellido, p.RFC
             FROM TarjetasCirculacion t
             INNER JOIN Vehiculos v ON v.IdVehiculo = t.IdVehiculo
             INNER JOIN Propietarios p ON p.IdPropietario = t.IdPropietario
             WHERE v.Matricula = :matricula
             ORDER BY t.IdTarjeta DESC
             LIMIT 1'
        );

        $consulta->execute(['matricula' => $matricula]);
        $fila = $consulta->fetch();

        return $fila ?: null;
    }

    /**
     * Renueva la tarjeta: extiende un año desde hoy, cambia estatus a Vigente
     * y genera un nuevo folio.
     */
    public function renovar(int $idTarjeta): bool
    {
        $hoy   = date('Y-m-d');
        $vence = date('Y-m-d', strtotime('+1 year'));
        $folio = 'TC-' . strtoupper(bin2hex(random_bytes(5)));

        $consulta = $this->db->prepare(
            'UPDATE TarjetasCirculacion
             SET NumeroFolio      = :folio,
                 FechaExpedicion  = :expedicion,
                 FechaVencimiento = :vencimiento,
                 Estatus          = :estatus
             WHERE IdTarjeta = :id'
        );

        $consulta->execute([
            'folio'      => $folio,
            'expedicion' => $hoy,
            'vencimiento'=> $vence,
            'estatus'    => 'Vigente',
            'id'         => $idTarjeta,
        ]);

        return $consulta->rowCount() === 1;
    }

    /**
     * Actualiza el estatus de tarjetas vencidas (FechaVencimiento < hoy).
     */
    public function actualizarVencidas(): void
    {
        $this->db->query(
            "UPDATE TarjetasCirculacion
             SET Estatus = 'Vencida'
             WHERE FechaVencimiento < CURDATE()
               AND Estatus = 'Vigente'"
        );
    }
}