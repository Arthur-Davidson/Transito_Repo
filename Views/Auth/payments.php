<?php
require_once __DIR__ . '/../../Config/auth.php';
require_once __DIR__ . '/../../Models/payment.php';
Auth::requerirSesion();

$error = '';
$datos = [
    'folio' => '',
    'monto' => '',
    'fecha' => date('Y-m-d'),
    'concepto' => '',
    'estatus' => 'Pendiente',
    'vehiculo' => ''
];
$modelo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($datos as $campo => $valor) {
        $datos[$campo] = trim(datoPost($campo));
    }
    $monto = filter_var($datos['monto'], FILTER_VALIDATE_FLOAT);
    $fecha = DateTime::createFromFormat('!Y-m-d', $datos['fecha']);
    $vehiculo = filter_var($datos['vehiculo'], FILTER_VALIDATE_INT);

    if (!Auth::validarToken()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } elseif (
        $datos['folio'] === '' || strlen($datos['folio']) > 30
        || $monto === false || $monto <= 0 || $monto > 99999999.99
        || !$fecha || $fecha->format('Y-m-d') !== $datos['fecha']
        || $datos['concepto'] === '' || strlen($datos['concepto']) > 100
        || !in_array($datos['estatus'], ['Pendiente', 'Pagado'], true)
        || $vehiculo === false || $vehiculo < 1
    ) {
        $error = 'Revisa el folio, monto, fecha, concepto y vehículo.';
    } else {
        try {
            $modelo = new Pago();
            $guardado = $modelo->crear([
                'folio' => $datos['folio'],
                'monto' => number_format($monto, 2, '.', ''),
                'fecha' => $datos['fecha'],
                'concepto' => $datos['concepto'],
                'estatus' => $datos['estatus'],
                'vehiculo' => $vehiculo
            ]);
            if ($guardado) {
                $_SESSION['mensaje_exito'] = 'Pago registrado correctamente.';
                header('Location: ' . BASE_URL . 'Views/Auth/payments.php');
                exit;
            }
            $error = 'El vehículo seleccionado ya no está disponible.';
        } catch (PDOException $ex) {
            error_log('Pagos: ' . $ex->getMessage());
            $error = 'No se pudo guardar el pago. Intenta más tarde.';
        }
    }
}

$exito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);
try {
    $modelo = new Pago();
    $pagos = $modelo->listar();
    $vehiculos = $modelo->opciones();
} catch (PDOException $ex) {
    error_log('Consulta de pagos: ' . $ex->getMessage());
    $pagos = [];
    $vehiculos = [];
    $error = 'No se pudieron cargar los pagos. Revisa la conexión a la base de datos.';
}

$titulo = 'Pagos';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Pagos</h1>
    <p>Registra pagos y consulta su estado.</p>
    <?php if ($exito !== ''): ?><div class="alert alert-success" role="status"><?= escapar($exito) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= escapar($error) ?></div><?php endif; ?>
    <section class="card auth-card mb-4"><div class="card-body">
        <h2 class="h4">Registrar pago</h2>
        <?php if (!$vehiculos): ?><p class="aviso">Registra primero un vehículo.</p><a class="btn btn-primary" href="<?= BASE_URL ?>Views/Auth/vehicles.php">Ir a vehículos</a><?php else: ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf" value="<?= escapar(Auth::token()) ?>">
            <div><label class="form-label" for="folio">Folio de pago</label><input class="form-control" id="folio" name="folio" maxlength="30" value="<?= escapar($datos['folio']) ?>" required></div>
            <div><label class="form-label" for="monto">Monto</label><input class="form-control" id="monto" name="monto" type="number" min="0.01" max="99999999.99" step="0.01" value="<?= escapar($datos['monto']) ?>" required></div>
            <div><label class="form-label" for="fecha">Fecha de pago</label><input class="form-control" id="fecha" name="fecha" type="date" value="<?= escapar($datos['fecha']) ?>" required></div>
            <div><label class="form-label" for="estatus">Estado</label><select class="form-control" id="estatus" name="estatus"><option value="Pendiente" <?= $datos['estatus'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option><option value="Pagado" <?= $datos['estatus'] === 'Pagado' ? 'selected' : '' ?>>Pagado</option></select></div>
            <div class="form-grid-full"><label class="form-label" for="concepto">Concepto</label><input class="form-control" id="concepto" name="concepto" maxlength="100" value="<?= escapar($datos['concepto']) ?>" required></div>
            <div class="form-grid-full"><label class="form-label" for="vehiculo">Vehículo</label><select class="form-control" id="vehiculo" name="vehiculo" required><option value="">Selecciona un vehículo</option><?php foreach ($vehiculos as $vehiculo): ?><option value="<?= (int) $vehiculo['IdVehiculo'] ?>" <?= $datos['vehiculo'] === (string) $vehiculo['IdVehiculo'] ? 'selected' : '' ?>><?= escapar($vehiculo['Matricula'] . ' — ' . $vehiculo['Marca'] . ' ' . $vehiculo['Modelo']) ?></option><?php endforeach; ?></select></div>
            <div class="form-grid-full"><button class="btn btn-primary" type="submit">Guardar pago</button></div>
        </form><?php endif; ?>
    </div></section>
    <section class="card auth-card"><div class="card-body">
        <h2 class="h4">Pagos registrados</h2>
        <div class="table-responsive"><table class="table"><thead><tr><th>Folio</th><th>Fecha</th><th>Concepto</th><th>Monto</th><th>Matrícula</th><th>Estado</th></tr></thead><tbody>
        <?php foreach ($pagos as $pago): ?><tr><td><?= escapar($pago['FolioPago']) ?></td><td><?= escapar($pago['FechaPago']) ?></td><td><?= escapar($pago['Concepto']) ?></td><td>$<?= number_format((float) $pago['Monto'], 2) ?></td><td><?= escapar($pago['Matricula']) ?></td><td><?= escapar($pago['Estatus']) ?></td></tr><?php endforeach; ?>
        <?php if (!$pagos): ?><tr><td colspan="6">Aún no hay pagos registrados.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div></section>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
