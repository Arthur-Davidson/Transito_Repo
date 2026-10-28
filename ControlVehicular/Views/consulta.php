<?php
require_once __DIR__ . '/../Config/auth.php';
require_once __DIR__ . '/../Config/database.php';

$pdo = Database::getInstance();

$plate = '';
$debts = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate = trim(postValue('plate'));

    if ($plate !== '') {
        $sql = "SELECT Vehiculos.Matricula, Vehiculos.Marca, Vehiculos.Modelo,
                       Propietarios.Nombre, Propietarios.Apellido,
                       Pagos.Monto, Pagos.Estatus
                FROM Vehiculos
                INNER JOIN Propietarios
                    ON Vehiculos.IdPropietario = Propietarios.IdPropietario
                INNER JOIN Pagos
                    ON Vehiculos.IdVehiculo = Pagos.IdVehiculo
                WHERE Vehiculos.Matricula = :plate";

        $query = $pdo->prepare($sql);
        $query->execute(['plate' => $plate]);

        $debts = $query->fetchAll();

        if (empty($debts)) {
            $message = 'No se encontraron adeudos pendientes para esta matrícula.';
        }
    } else {
        $message = 'Introduce una matrícula.';
    }
}

$title = 'Consulta de adeudos';
include __DIR__ . '/header.php';
?>
<main class="container py-5">
    <h1>Consulta de adeudos</h1>

    <form method="post" action="">
        <label for="plate">Introduce tu matrícula</label>
        <input
            type="text"
            id="plate"
            name="plate"
            value="<?= escape($plate) ?>"
            required
        >
        <button type="submit">Consultar</button>
    </form>

    <h2>Resultado:</h2>

    <?php if ($message !== ''): ?>
        <p><?= escape($message) ?></p>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Vehículo</th>
                <th>Propietario</th>
                <th>Adeudo</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($debts)): ?>
                <?php foreach ($debts as $debt): ?>
                    <tr>
                        <td><?= escape($debt['Matricula']) ?></td>
                        <td><?= escape($debt['Marca'] . ' ' . $debt['Modelo']) ?></td>
                        <td><?= escape($debt['Nombre'] . ' ' . $debt['Apellido']) ?></td>
                        <td>$<?= number_format($debt['Monto'], 2) ?></td>
                        <td><?= escape($debt['Estatus']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Sin resultados para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
<?php include __DIR__ . '/footer.php'; ?>
