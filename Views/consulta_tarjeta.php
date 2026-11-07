<?php
require_once __DIR__ . '/../Config/auth.php';
require_once __DIR__ . '/../Models/card.php';
Auth::iniciar();

$matricula  = '';
$tarjeta    = null;
$error      = '';
$consultado = false;

if (isset($_GET['matricula']) && is_string($_GET['matricula'])) {
    $matricula  = strtoupper(trim($_GET['matricula']));
    $consultado = $matricula !== '';
} elseif (isset($_GET['matricula'])) {
    $error = 'Ingresa una matrícula válida.';
}

if ($consultado) {
    if (strlen($matricula) > 20) {
        $error = 'La matrícula no puede exceder 20 caracteres.';
    } else {
        try {
            $modelo  = new TarjetaCirculacion();
            $modelo->actualizarVencidas();
            $tarjeta = $modelo->buscarPorMatricula($matricula);
            if (!$tarjeta) {
                $error = 'No se encontró ninguna tarjeta para la matrícula <strong>' . escapar($matricula) . '</strong>.';
            }
        } catch (PDOException $ex) {
            error_log('Consulta pública tarjeta: ' . $ex->getMessage());
            $error = 'No se pudo completar la consulta. Intenta más tarde.';
        }
    }
}

$titulo = 'Consultar tarjeta de circulación';
include __DIR__ . '/header.php';
?>
<main class="container py-5">
    <div class="row justify-content-center"><div class="col-12 col-lg-8">
        <section class="card auth-card"><div class="card-body">
            <h1>Consultar tarjeta de circulación</h1>
            <p>Ingresa la matrícula del vehículo para ver su tarjeta de circulación.</p>

            <form method="get" class="consulta-form">
                <div>
                    <label class="form-label" for="matricula">Matrícula del vehículo</label>
                    <input class="form-control" id="matricula" name="matricula"
                           maxlength="20" value="<?= escapar($matricula) ?>"
                           placeholder="Ej. ABC123A" required>
                </div>
                <button class="btn btn-primary" type="submit">Consultar</button>
            </form>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger mt-4" role="alert"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($tarjeta): ?>
            <?php
                $hoy     = new DateTime();
                $vence   = DateTime::createFromFormat('Y-m-d', $tarjeta['FechaVencimiento']);
                $vencida = $tarjeta['Estatus'] === 'Vencida' || ($vence && $vence < $hoy);
                $badgeClase = $vencida ? 'badge-vencida' : 'badge-vigente';
                $badgeTexto = $vencida ? 'Vencida' : 'Vigente';
            ?>
            <div class="resultado-consulta mt-4">
                <div class="tarjeta-header mb-3">
                    <div>
                        <h2 class="h4 mb-0">Tarjeta de Circulación</h2>
                        <span class="folio-num"><?= escapar($tarjeta['NumeroFolio']) ?></span>
                    </div>
                    <span class="badge-estatus <?= $badgeClase ?>"><?= $badgeTexto ?></span>
                </div>

                <dl class="datos-consulta">
                    <div><dt>Matrícula</dt><dd><?= escapar($tarjeta['Matricula']) ?></dd></div>
                    <div><dt>Vehículo</dt><dd><?= escapar($tarjeta['Marca'] . ' ' . $tarjeta['Modelo']) ?></dd></div>
                    <div><dt>Año</dt><dd><?= (int) $tarjeta['Anio'] ?></dd></div>
                    <div><dt>Color</dt><dd><?= escapar($tarjeta['Color'] ?? 'No especificado') ?></dd></div>
                    <div><dt>Propietario</dt><dd><?= escapar($tarjeta['Nombre'] . ' ' . $tarjeta['Apellido']) ?></dd></div>
                    <div><dt>RFC</dt><dd><?= escapar($tarjeta['RFC']) ?></dd></div>
                    <div><dt>Fecha de expedición</dt><dd><?= escapar($tarjeta['FechaExpedicion']) ?></dd></div>
                    <div><dt>Fecha de vencimiento</dt><dd><?= escapar($tarjeta['FechaVencimiento']) ?></dd></div>
                </dl>

                <?php if ($vencida): ?>
                <p class="aviso mt-4">
                    ⚠️ Esta tarjeta está <strong>vencida</strong>.
                    Para renovarla, acude a las oficinas de tránsito o contacta a un administrador.
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div></section>
    </div></div>
</main>
<?php include __DIR__ . '/footer.php'; ?>

