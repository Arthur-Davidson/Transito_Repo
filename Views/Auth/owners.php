<?php
require_once __DIR__ . '/../../Config/auth.php';
require_once __DIR__ . '/../../Models/owner.php';
Auth::requerirAdmin();

$error = '';
$nombre = '';
$apellido = '';
$rfc = '';
$telefono = '';
$direccion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(datoPost('nombre'));
    $apellido = trim(datoPost('apellido'));
    $rfc = strtoupper(trim(datoPost('rfc')));
    $telefono = trim(datoPost('telefono'));
    $direccion = trim(datoPost('direccion'));

    if (!Auth::validarToken()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } elseif (
        $nombre === '' || strlen($nombre) > 100
        || $apellido === '' || strlen($apellido) > 100
        || $rfc === '' || strlen($rfc) > 13
        || strlen($telefono) > 15 || strlen($direccion) > 255
    ) {
        $error = 'Revisa los campos obligatorios y sus longitudes.';
    } else {
        try {
            (new Propietario())->crear([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'rfc' => $rfc,
                'telefono' => $telefono ?: null,
                'direccion' => $direccion ?: null
            ]);
            $_SESSION['mensaje_exito'] = 'Propietario registrado correctamente.';
            header('Location: ' . BASE_URL . 'Views/Auth/owners.php');
            exit;
        } catch (PDOException $ex) {
            error_log('Propietarios: ' . $ex->getMessage());
            $error = 'No se pudo guardar el propietario. Intenta más tarde.';
        }
    }
}

$exito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);
try {
    $propietarios = (new Propietario())->listar();
} catch (PDOException $ex) {
    error_log('Consulta de propietarios: ' . $ex->getMessage());
    $propietarios = [];
    $error = 'No se pudieron cargar los propietarios. Revisa la conexión a la base de datos.';
}

$titulo = 'Propietarios';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Propietarios</h1>
    <p>Registra y consulta los propietarios de los vehículos.</p>
    <?php if ($exito !== ''): ?><div class="alert alert-success" role="status"><?= escapar($exito) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= escapar($error) ?></div><?php endif; ?>

    <section class="card auth-card mb-4">
        <div class="card-body">
            <h2 class="h4">Registrar propietario</h2>
            <form method="post" class="form-grid">
                <input type="hidden" name="csrf" value="<?= escapar(Auth::token()) ?>">
                <div><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" maxlength="100" value="<?= escapar($nombre) ?>" required></div>
                <div><label class="form-label" for="apellido">Apellido</label><input class="form-control" id="apellido" name="apellido" maxlength="100" value="<?= escapar($apellido) ?>" required></div>
                <div><label class="form-label" for="rfc">RFC</label><input class="form-control" id="rfc" name="rfc" maxlength="13" value="<?= escapar($rfc) ?>" required></div>
                <div><label class="form-label" for="telefono">Teléfono</label><input class="form-control" id="telefono" name="telefono" maxlength="15" value="<?= escapar($telefono) ?>"></div>
                <div class="form-grid-full"><label class="form-label" for="direccion">Dirección</label><input class="form-control" id="direccion" name="direccion" maxlength="255" value="<?= escapar($direccion) ?>"></div>
                <div class="form-grid-full"><button class="btn btn-primary" type="submit">Guardar propietario</button></div>
            </form>
        </div>
    </section>

    <section class="card auth-card">
        <div class="card-body">
            <h2 class="h4">Propietarios registrados</h2>
            <div class="table-responsive"><table class="table">
                <thead><tr><th>Nombre</th><th>RFC</th><th>Teléfono</th><th>Dirección</th></tr></thead>
                <tbody>
                <?php foreach ($propietarios as $propietario): ?>
                    <tr><td><?= escapar($propietario['Nombre'] . ' ' . $propietario['Apellido']) ?></td><td><?= escapar($propietario['RFC']) ?></td><td><?= escapar($propietario['Telefono'] ?? '') ?></td><td><?= escapar($propietario['Direccion'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$propietarios): ?><tr><td colspan="4">Aún no hay propietarios registrados.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div>
    </section>
</main>
<?php include __DIR__ . '/../footer.php'; ?>
