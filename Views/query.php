<?php
require_once __DIR__ . '/../Config/auth.php';
Auth::iniciar();

$matricula = isset($_GET['matricula']) && is_string($_GET['matricula'])
    ? strtoupper(trim($_GET['matricula']))
    : '';
$vehiculo = null;
$error = '';
$consultado = $matricula !== '';

if (isset($_GET['matricula']) && !is_string($_GET['matricula'])) {
    $error = 'Ingresa una matrícula válida.';
} elseif ($consultado && strlen($matricula) <= 20) {
    try {
        require_once __DIR__ . '/../Config/database.php';
        $db = Database::getInstance();
        $consulta = $db->prepare(
            'SELECT v.Matricula, v.Marca, v.Modelo, v.Anio, v.Color,
                    t.NumeroFolio, t.FechaVencimiento, t.Estatus
             FROM Vehiculos v
             LEFT JOIN TarjetasCirculacion t
                ON t.IdTarjeta = (
                    SELECT MAX(t2.IdTarjeta)
                    FROM TarjetasCirculacion t2
                    WHERE t2.IdVehiculo = v.IdVehiculo
                )
             WHERE v.Matricula = :matricula
             LIMIT 1'
        );
        $consulta->execute(['matricula' => $matricula]);
        $vehiculo = $consulta->fetch();
    } catch (PDOException $ex) {
        error_log('Consulta pública: ' . $ex->getMessage());
        $error = 'No se pudo completar la consulta. Intenta más tarde.';
    }
} elseif ($consultado) {
    $error = 'La matrícula no puede exceder 20 caracteres.';
}

$titulo = 'Consulta vehicular';
include __DIR__ . '/header.php';
?>
<main class="container py-5">
    <div class="row justify-content-center"><div class="col-12 col-lg-8">
        <section class="card auth-card"><div class="card-body">
            <h1>Consulta vehicular</h1>
            <p>Consulta la información pública de un vehículo por matrícula.</p>
            <form method="get" class="consulta-form">
                <div><label class="form-label" for="matricula">Matrícula</label><input class="form-control" id="matricula" name="matricula" maxlength="20" value="<?= escapar($matricula) ?>" required></div>
                <button class="btn btn-primary" type="submit">Consultar</button>
            </form>
            <?php if ($error !== ''): ?><div class="alert alert-danger mt-4" role="alert"><?= escapar($error) ?></div><?php endif; ?>
            <?php if ($consultado && $error === '' && !$vehiculo): ?><div class="alert alert-secondary mt-4" role="status">No se encontró un vehículo con esa matrícula.</div><?php endif; ?>
            <?php if ($vehiculo): ?>
                <div class="resultado-consulta mt-4">
                    <h2 class="h4">Resultado</h2>
                    <dl class="datos-consulta">
                        <div><dt>Matrícula</dt><dd><?= escapar($vehiculo['Matricula']) ?></dd></div>
                        <div><dt>Vehículo</dt><dd><?= escapar($vehiculo['Marca'] . ' ' . $vehiculo['Modelo']) ?></dd></div>
                        <div><dt>Año</dt><dd><?= (int) $vehiculo['Anio'] ?></dd></div>
                        <div><dt>Color</dt><dd><?= escapar($vehiculo['Color'] ?? 'No especificado') ?></dd></div>
                        <div><dt>Tarjeta</dt><dd><?= escapar($vehiculo['NumeroFolio'] ?? 'Sin tarjeta registrada') ?></dd></div>
                        <div><dt>Vencimiento</dt><dd><?= escapar($vehiculo['FechaVencimiento'] ?? 'No disponible') ?></dd></div>
                        <div><dt>Estado de tarjeta</dt><dd><?= escapar($vehiculo['Estatus'] ?? 'No disponible') ?></dd></div>
                    </dl>
                </div>
            <?php endif; ?>
        </div></section>
    </div></div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
