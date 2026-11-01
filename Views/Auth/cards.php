<?php
require_once __DIR__ . '/../../Config/auth.php';
require_once __DIR__ . '/../../Models/card.php';
Auth::requerirSesion();

$error = '';
$datos = [
    'folio' => '',
    'expedicion' => '',
    'vencimiento' => '',
    'estatus' => 'Vigente',
    'vehiculo' => ''
];
$modelo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($datos as $campo => $valor) {
        $datos[$campo] = trim(datoPost($campo));
    }

    $fechaExpedicion = DateTime::createFromFormat('!Y-m-d', $datos['expedicion']);
    $fechaVencimiento = DateTime::createFromFormat('!Y-m-d', $datos['vencimiento']);
    $vehiculo = filter_var($datos['vehiculo'], FILTER_VALIDATE_INT);

    if (!Auth::validarToken()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } elseif (
        $datos['folio'] === '' || strlen($datos['folio']) > 30
        || !$fechaExpedicion || $fechaExpedicion->format('Y-m-d') !== $datos['expedicion']
        || !$fechaVencimiento || $fechaVencimiento->format('Y-m-d') !== $datos['vencimiento']
        || $fechaVencimiento <= $fechaExpedicion
        || !in_array($datos['estatus'], ['Vigente', 'Vencida', 'Suspendida'], true)
        || $vehiculo === false || $vehiculo < 1
    ) {
        $error = 'Revisa el folio, las fechas y el estado de la tarjeta.';
    } else {
        try {
            $modelo = new TarjetaCirculacion();
            $guardado = $modelo->crear([
                'folio' => $datos['folio'],
                'expedicion' => $datos['expedicion'],
                'vencimiento' => $datos['vencimiento'],
                'estatus' => $datos['estatus'],
                'vehiculo' => $vehiculo
            ]);
            if ($guardado) {
                $_SESSION['mensaje_exito'] = 'Tarjeta registrada correctamente.';
                header('Location: ' . BASE_URL . 'Views/Auth/cards.php');
                exit;
            }
            $error = 'El vehículo seleccionado ya no está disponible.';
        } catch (PDOException $ex) {
            error_log('Tarjetas: ' . $ex->getMessage());
            $error = 'No se pudo guardar la tarjeta. Intenta más tarde.';
        }
    }
}

$exito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);
try {
    $modelo = new TarjetaCirculacion();
    $tarjetas = $modelo->listar();
    $vehiculos = $modelo->opciones();
} catch (PDOException $ex) {
    error_log('Consulta de tarjetas: ' . $ex->getMessage());
    $tarjetas = [];
    $vehiculos = [];
    $error = 'No se pudieron cargar las tarjetas. Revisa la conexión a la base de datos.';
}

$titulo = 'Tarjetas de circulación';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Tarjetas de circulación</h1>
    <p>Registra tarjetas y consulta su vigencia.</p>
    <?php if ($exito !== ''): ?><div class="alert alert-success" role="status"><?= escapar($exito) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= escapar($error) ?></div><?php endif; ?>
    <section class="card auth-card mb-4"><div class="card-body">
        <h2 class="h4">Registrar tarjeta</h2>
        <?php if (!$vehiculos): ?><p class="aviso">Registra primero un vehículo.</p><a class="btn btn-primary" href="<?= BASE_URL ?>Views/Auth/vehicles.php">Ir a vehículos</a><?php else: ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf" value="<?= escapar(Auth::token()) ?>">
            <div><label class="form-label" for="folio">Número de folio</label><input class="form-control" id="folio" name="folio" maxlength="30" value="<?= escapar($datos['folio']) ?>" required></div>
            <div><label class="form-label" for="estatus">Estado</label><select class="form-control" id="estatus" name="estatus"><?php foreach (['Vigente', 'Vencida', 'Suspendida'] as $estatus): ?><option value="<?= escapar($estatus) ?>" <?= $datos['estatus'] === $estatus ? 'selected' : '' ?>><?= escapar($estatus) ?></option><?php endforeach; ?></select></div>
            <div><label class="form-label" for="expedicion">Fecha de expedición</label><input class="form-control" id="expedicion" name="expedicion" type="date" value="<?= escapar($datos['expedicion']) ?>" required></div>
            <div><label class="form-label" for="vencimiento">Fecha de vencimiento</label><input class="form-control" id="vencimiento" name="vencimiento" type="date" value="<?= escapar($datos['vencimiento']) ?>" required></div>
            <div class="form-grid-full"><label class="form-label" for="vehiculo">Vehículo</label><select class="form-control" id="vehiculo" name="vehiculo" required><option value="">Selecciona un vehículo</option><?php foreach ($vehiculos as $vehiculo): ?><option value="<?= (int) $vehiculo['IdVehiculo'] ?>" <?= $datos['vehiculo'] === (string) $vehiculo['IdVehiculo'] ? 'selected' : '' ?>><?= escapar($vehiculo['Matricula'] . ' — ' . $vehiculo['Marca'] . ' ' . $vehiculo['Modelo']) ?></option><?php endforeach; ?></select></div>
            <div class="form-grid-full"><button class="btn btn-primary" type="submit">Guardar tarjeta</button></div>
        </form><?php endif; ?>
    </div></section>
    <section class="card auth-card"><div class="card-body">
        <h2 class="h4">Tarjetas registradas</h2>
        <div class="table-responsive"><table class="table"><thead><tr><th>Folio</th><th>Matrícula</th><th>Propietario</th><th>Vigencia</th><th>Estado</th></tr></thead><tbody>
        <?php foreach ($tarjetas as $tarjeta): ?><tr><td><?= escapar($tarjeta['NumeroFolio']) ?></td><td><?= escapar($tarjeta['Matricula']) ?></td><td><?= escapar($tarjeta['Nombre'] . ' ' . $tarjeta['Apellido']) ?></td><td><?= escapar($tarjeta['FechaExpedicion']) ?> — <?= escapar($tarjeta['FechaVencimiento']) ?></td><td><?= escapar($tarjeta['Estatus']) ?></td></tr><?php endforeach; ?>
        <?php if (!$tarjetas): ?><tr><td colspan="5">Aún no hay tarjetas registradas.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div></section>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
