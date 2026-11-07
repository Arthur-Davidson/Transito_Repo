<?php
require_once __DIR__ . '/../../Config/auth.php';
require_once __DIR__ . '/../../Models/card.php';
Auth::requerirAdmin();

$error  = '';
$exito  = '';
$tarjeta = null;
$matriculaBuscada = '';
$consultado = false;

$modelo = new TarjetaCirculacion();

// Actualizar automáticamente tarjetas vencidas
try {
    $modelo->actualizarVencidas();
} catch (PDOException $ex) {
    error_log('Actualizar vencidas: ' . $ex->getMessage());
}

// ── Renovar tarjeta (solo administradores) ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'renovar') {
    if (!Auth::esAdmin()) {
        $error = 'Solo los administradores pueden renovar tarjetas.';
    } elseif (!Auth::validarToken()) {
        $error = 'El formulario expiró. Intenta nuevamente.';
    } else {
        $idTarjeta = filter_var($_POST['id_tarjeta'] ?? '', FILTER_VALIDATE_INT);
        if ($idTarjeta === false || $idTarjeta < 1) {
            $error = 'Tarjeta inválida.';
        } else {
            try {
                if ($modelo->renovar($idTarjeta)) {
                    $_SESSION['mensaje_exito'] = 'Tarjeta renovada correctamente. Nueva vigencia: un año desde hoy.';
                } else {
                    $error = 'No se encontró la tarjeta o ya estaba actualizada.';
                }
            } catch (PDOException $ex) {
                error_log('Renovar tarjeta: ' . $ex->getMessage());
                $error = 'No se pudo renovar la tarjeta. Intenta más tarde.';
            }
        }
    }

    // Redirigir para evitar reenvío del formulario, conservando la matrícula buscada
    $matriculaRedir = strtoupper(trim($_POST['matricula_busqueda'] ?? ''));
    if ($error === '') {
        header('Location: ' . BASE_URL . 'Views/Auth/cards.php?matricula=' . urlencode($matriculaRedir));
        exit;
    }
    // Si hay error, mantener matrícula para mostrar el resultado
    $matriculaBuscada = $matriculaRedir;
    $consultado = $matriculaBuscada !== '';
}

// ── Éxito desde redirección ───────────────────────────────────────────────────
$exito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);

// ── Búsqueda por GET ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['matricula'])) {
    $matriculaBuscada = strtoupper(trim((string) $_GET['matricula']));
    $consultado = $matriculaBuscada !== '';
}

if ($consultado) {
    if (strlen($matriculaBuscada) > 20) {
        $error = 'La matrícula no puede exceder 20 caracteres.';
    } else {
        try {
            $tarjeta = $modelo->buscarPorMatricula($matriculaBuscada);
            if (!$tarjeta) {
                $error = 'No se encontró ninguna tarjeta para la matrícula <strong>' . escapar($matriculaBuscada) . '</strong>.';
            }
        } catch (PDOException $ex) {
            error_log('Consulta tarjeta: ' . $ex->getMessage());
            $error = 'No se pudo completar la consulta. Intenta más tarde.';
        }
    }
}

$titulo = 'Tarjetas de circulación';
include __DIR__ . '/../header.php';
?>
<main class="container py-5">
    <h1>Tarjetas de circulación</h1>
    <p>Busca una matrícula para consultar la tarjeta de circulación del vehículo.</p>

    <?php if ($exito !== ''): ?>
        <div class="alert alert-success" role="status"><?= $exito ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= $error ?></div>
    <?php endif; ?>

    <!-- Formulario de búsqueda -->
    <section class="card auth-card mb-4"><div class="card-body">
        <h2 class="h4">Consultar tarjeta por matrícula</h2>
        <form method="get" class="consulta-form">
            <div>
                <label class="form-label" for="matricula">Matrícula del vehículo</label>
                <input class="form-control" id="matricula" name="matricula"
                       maxlength="20" value="<?= escapar($matriculaBuscada) ?>"
                       placeholder="Ej. ABC123A" required>
            </div>
            <button class="btn btn-primary" type="submit">Consultar</button>
        </form>
    </div></section>

    <!-- Resultado -->
    <?php if ($tarjeta): ?>
    <?php
        $hoy      = new DateTime();
        $vence    = DateTime::createFromFormat('Y-m-d', $tarjeta['FechaVencimiento']);
        $vencida  = $tarjeta['Estatus'] === 'Vencida' || ($vence && $vence < $hoy);
        $badgeClase = $vencida ? 'badge-vencida' : 'badge-vigente';
        $badgeTexto = $vencida ? 'Vencida' : 'Vigente';
    ?>
    <section class="card auth-card tarjeta-resultado">
        <div class="card-body">
            <div class="tarjeta-header">
                <div>
                    <h2 class="h4 mb-0">Tarjeta de Circulación</h2>
                    <span class="folio-num"><?= escapar($tarjeta['NumeroFolio']) ?></span>
                </div>
                <span class="badge-estatus <?= $badgeClase ?>"><?= $badgeTexto ?></span>
            </div>

            <dl class="datos-consulta mt-4">
                <div><dt>Matrícula</dt><dd><?= escapar($tarjeta['Matricula']) ?></dd></div>
                <div><dt>Vehículo</dt><dd><?= escapar($tarjeta['Marca'] . ' ' . $tarjeta['Modelo']) ?></dd></div>
                <div><dt>Año</dt><dd><?= (int) $tarjeta['Anio'] ?></dd></div>
                <div><dt>Color</dt><dd><?= escapar($tarjeta['Color'] ?? 'No especificado') ?></dd></div>
                <div><dt>Propietario</dt><dd><?= escapar($tarjeta['Nombre'] . ' ' . $tarjeta['Apellido']) ?></dd></div>
                <div><dt>RFC</dt><dd><?= escapar($tarjeta['RFC']) ?></dd></div>
                <div><dt>Fecha de expedición</dt><dd><?= escapar($tarjeta['FechaExpedicion']) ?></dd></div>
                <div><dt>Fecha de vencimiento</dt><dd><?= escapar($tarjeta['FechaVencimiento']) ?></dd></div>
            </dl>

            <?php if ($vencida && Auth::esAdmin()): ?>
            <div class="renovar-box mt-4">
                <p class="aviso">
                    ⚠️ Esta tarjeta está <strong>vencida</strong>. Como administrador puedes renovarla
                    registrando el pago de placas correspondiente.
                </p>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= escapar(Auth::token()) ?>">
                    <input type="hidden" name="accion" value="renovar">
                    <input type="hidden" name="id_tarjeta" value="<?= (int) $tarjeta['IdTarjeta'] ?>">
                    <input type="hidden" name="matricula_busqueda" value="<?= escapar($tarjeta['Matricula']) ?>">
                    <button class="btn btn-warning" type="submit">
                        💳 Renovar pago de placas y tarjeta
                    </button>
                </form>
            </div>
            <?php elseif ($vencida && !Auth::esAdmin()): ?>
            <p class="aviso mt-4">
                ⚠️ Esta tarjeta está <strong>vencida</strong>. Contacta a un administrador para renovarla.
            </p>
            <?php endif; ?>
        </div>
    </section>
    <?php elseif ($consultado && $error === ''): ?>
        <div class="alert alert-secondary" role="status">
            No se encontró ninguna tarjeta de circulación para la matrícula
            <strong><?= escapar($matriculaBuscada) ?></strong>.
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../footer.php'; ?>

