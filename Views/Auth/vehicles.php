<?php
require_once __DIR__ . '/../../Config/auth.php';
require_once __DIR__ . '/../../Models/vehicle.php';
Auth::requerirSesion();

$error = '';
$datos = [
    'matricula' => '',
    'numero_serie' => '',
    'marca' => '',
    'modelo' => '',
    'anio' => '',
    'color' => '',
    'propietario' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($datos as $campo => $valor) {
        $datos[$campo] = trim(datoPost($campo));
    }
    $anio = filter_var($datos['anio'], FILTER_VALIDATE_INT);
    $propietario = filter_var($datos['propietario'], FILTER_VALIDATE_INT);

    if (!Auth::validarToken()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } elseif (
        $datos['matricula'] === '' || strlen($datos['matricula']) > 20
        || $datos['numero_serie'] === '' || strlen($datos['numero_serie']) > 50
        || $datos['marca'] === '' || strlen($datos['marca']) > 50
        || $datos['modelo'] === '' || strlen($datos['modelo']) > 50
        || $anio === false || $anio < 1900 || $anio > (int) date('Y') + 1
        || strlen($datos['color']) > 30
        || $propietario === false || $propietario < 1
    ) {
        $error = 'Revisa los campos obligatorios y sus valores.';
    } else {
        try {
            (new Vehiculo())->crear([
                'matricula' => strtoupper($datos['matricula']),
                'numero_serie' => $datos['numero_serie'],
                'marca' => $datos['marca'],
                'modelo' => $datos['modelo'],
                'anio' => $anio,
                'color' => $datos['color'] ?: null,
                'propietario' => $propietario
            ]);
            $_SESSION['mensaje_exito'] = 'Vehículo registrado correctamente.';
            header('Location: ' . BASE_URL . 'Views/Auth/vehicles.php');
            exit;
        } catch (PDOException $ex) {
            error_log('Vehículos: ' . $ex->getMessage());
            $error = 'No se pudo guardar el vehículo. Verifica sus datos y el propietario.';
        }
    }
}

$exito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);
try {
    $modelo = new Vehiculo();
    $vehiculos = $modelo->listar();
    $propietarios = $modelo->propietarios();
} catch (PDOException $ex) {
    error_log('Consulta de vehículos: ' . $ex->getMessage());
    $vehiculos = [];
    $propietarios = [];
    $error = 'No se pudieron cargar los vehículos. Revisa la conexión a la base de datos.';
}

$titulo = 'Vehículos';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Vehículos</h1>
    <p>Registra vehículos vinculándolos con un propietario existente.</p>
    <?php if ($exito !== ''): ?><div class="alert alert-success" role="status"><?= escapar($exito) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= escapar($error) ?></div><?php endif; ?>

    <section class="card auth-card mb-4"><div class="card-body">
        <h2 class="h4">Registrar vehículo</h2>
        <?php if (!$propietarios): ?>
            <p class="aviso">Primero registra un propietario para poder asociarlo a un vehículo.</p>
            <a class="btn btn-primary" href="<?= BASE_URL ?>Views/Auth/owners.php">Ir a propietarios</a>
        <?php else: ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="csrf" value="<?= escapar(Auth::token()) ?>">
            <div><label class="form-label" for="matricula">Matrícula</label><input class="form-control" id="matricula" name="matricula" maxlength="20" value="<?= escapar($datos['matricula']) ?>" required></div>
            <div><label class="form-label" for="numero_serie">Número de serie</label><input class="form-control" id="numero_serie" name="numero_serie" maxlength="50" value="<?= escapar($datos['numero_serie']) ?>" required></div>
            <div><label class="form-label" for="marca">Marca</label><input class="form-control" id="marca" name="marca" maxlength="50" value="<?= escapar($datos['marca']) ?>" required></div>
            <div><label class="form-label" for="modelo">Modelo</label><input class="form-control" id="modelo" name="modelo" maxlength="50" value="<?= escapar($datos['modelo']) ?>" required></div>
            <div><label class="form-label" for="anio">Año</label><input class="form-control" id="anio" name="anio" type="number" min="1900" max="<?= (int) date('Y') + 1 ?>" value="<?= escapar($datos['anio']) ?>" required></div>
            <div><label class="form-label" for="color">Color</label><input class="form-control" id="color" name="color" maxlength="30" value="<?= escapar($datos['color']) ?>"></div>
            <div class="form-grid-full"><label class="form-label" for="propietario">Propietario</label><select class="form-control" id="propietario" name="propietario" required><option value="">Selecciona un propietario</option><?php foreach ($propietarios as $propietario): ?><option value="<?= (int) $propietario['IdPropietario'] ?>" <?= $datos['propietario'] === (string) $propietario['IdPropietario'] ? 'selected' : '' ?>><?= escapar($propietario['Apellido'] . ', ' . $propietario['Nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="form-grid-full"><button class="btn btn-primary" type="submit">Guardar vehículo</button></div>
        </form>
        <?php endif; ?>
    </div></section>

    <section class="card auth-card"><div class="card-body">
        <h2 class="h4">Vehículos registrados</h2>
        <div class="table-responsive"><table class="table">
            <thead><tr><th>Matrícula</th><th>Vehículo</th><th>Año</th><th>Propietario</th></tr></thead>
            <tbody><?php foreach ($vehiculos as $vehiculo): ?>
                <tr><td><?= escapar($vehiculo['Matricula']) ?></td><td><?= escapar($vehiculo['Marca'] . ' ' . $vehiculo['Modelo']) ?></td><td><?= (int) $vehiculo['Anio'] ?></td><td><?= escapar($vehiculo['Nombre'] . ' ' . $vehiculo['Apellido']) ?></td></tr>
            <?php endforeach; ?><?php if (!$vehiculos): ?><tr><td colspan="4">Aún no hay vehículos registrados.</td></tr><?php endif; ?></tbody>
        </table></div>
    </div></section>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
