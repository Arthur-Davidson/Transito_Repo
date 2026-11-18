
<?php
require_once __DIR__ . '/../Config/auth.php';
Auth::iniciar();

$matricula = isset($_GET['matricula']) && is_string($_GET['matricula'])
    ? strtoupper(trim($_GET['matricula']))
    : '';

$vehiculo = null;
$pagos = [];
$error = '';
$consultado = $matricula !== '';

if (isset($_GET['matricula']) && !is_string($_GET['matricula'])) {
    $error = 'Ingresa una matrícula válida.';
} elseif ($consultado && strlen($matricula) <= 20) {
    try {
        require_once __DIR__ . '/../Config/database.php';
        $db = Database::getInstance();

        // Buscar el vehículo mediante su matrícula
        $consulta = $db->prepare(
            'SELECT IdVehiculo, Matricula, Marca, Modelo, Color
             FROM Vehiculos
             WHERE Matricula = :matricula
             LIMIT 1'
        );

        $consulta->execute(['matricula' => $matricula]);
        $vehiculo = $consulta->fetch();

        // Si existe el vehículo, consultar sus pagos
        if ($vehiculo) {
            $consultaPagos = $db->prepare(
                'SELECT FolioPago, Monto, FechaPago, Concepto, Estatus
                 FROM Pagos
                 WHERE IdVehiculo = :idVehiculo
                 ORDER BY FechaPago DESC, IdPago DESC'
            );

            $consultaPagos->execute([
                'idVehiculo' => $vehiculo['IdVehiculo']
            ]);

            $pagos = $consultaPagos->fetchAll();
        }

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
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <section class="card auth-card">
                <div class="card-body">

                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h1>Consulta de Adeudos</h1>
                            <p>Consulta la información de adeudos introduciendo la matrícula.</p>

                            <form method="get" class="consulta-form">
                                <div>
                                    <label class="form-label" for="matricula">Matrícula</label>
                                    <input
                                        class="form-control"
                                        id="matricula"
                                        name="matricula"
                                        maxlength="20"
                                        value="<?= escapar($matricula) ?>"
                                        required
                                    >
                                </div>

                                <button class="btn btn-primary" type="submit">
                                    Consultar
                                </button>
                            </form>
                        </div>

                        <div class="col-md-5 text-center mt-4 mt-md-0">
                            <img
                                src="<?= BASE_URL ?>Img/img_02.png"
                                alt="Consulta vehicular"
                                class="img-fluid"
                                style="max-height: 180px;"
                            >
                        </div>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger mt-4" role="alert">
                            <?= escapar($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($consultado && $error === '' && !$vehiculo): ?>
                        <div class="alert alert-secondary mt-4" role="status">
                            No se encontró un vehículo con esa matrícula.
                        </div>
                    <?php endif; ?>

                    <?php if ($vehiculo): ?>

                        <div class="resultado-consulta mt-4">

                            <h2 class="h4">Información del vehículo</h2>

                            <div class="datos-consulta d-flex flex-wrap gap-4">
                                <div class="flex-fill">
                                    <dt>Matrícula</dt>
                                    <dd><?= escapar($vehiculo['Matricula']) ?></dd>
                                </div>

                                <div class="flex-fill">
                                    <dt>Marca</dt>
                                    <dd><?= escapar($vehiculo['Marca']) ?></dd>
                                </div>

                                <div class="flex-fill">
                                    <dt>Color</dt>
                                    <dd><?= escapar($vehiculo['Color'] ?? 'No especificado') ?></dd>
                                </div>
                            </div>

                            <h2 class="h4 mt-4">Adeudos del vehículo</h2>

                            <?php if ($pagos): ?>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Concepto</th>
                                                <th>Monto</th>
                                                <th>Fecha</th>
                                                <th>Estatus</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($pagos as $pago): ?>
                                                <tr>
                                                    <td>
                                                        <?= escapar($pago['FolioPago']) ?>
                                                    </td>

                                                    <td>
                                                        <?= escapar($pago['Concepto']) ?>
                                                    </td>

                                                    <td>
                                                        $<?= number_format((float) $pago['Monto'], 2) ?>
                                                    </td>

                                                    <td>
                                                        <?= escapar($pago['FechaPago']) ?>
                                                    </td>

                                                    <td>
                                                        <?php if (strtolower($pago['Estatus']) === 'pendiente'): ?>
                                                            <span class="badge bg-warning text-dark">
                                                                Pendiente
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">
                                                                <?= escapar($pago['Estatus']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php
                                $totalAdeudo = 0;

                                foreach ($pagos as $pago) {
                                    if (strtolower($pago['Estatus']) === 'pendiente') {
                                        $totalAdeudo += (float) $pago['Monto'];
                                    }
                                }
                                ?>

                                <div class="mt-3 text-end">
                                    <h3 class="h5">
                                        Total de adeudos:
                                        <strong>$<?= number_format($totalAdeudo, 2) ?></strong>
                                    </h3>
                                </div>

                            <?php else: ?>

                                <div class="alert alert-success" role="status">
                                    Este vehículo no tiene pagos registrados.
                                </div>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                </div>
            </section>

        </div>
    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
