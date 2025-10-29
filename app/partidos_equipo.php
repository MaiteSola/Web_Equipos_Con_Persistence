<?php
require_once __DIR__ . '/../persistence/DAO/PartidosDAO.php';
require_once __DIR__ . '/../persistence/DAO/EquiposDAO.php';
require_once __DIR__ . '/../utils/SessionHelper.php';
SessionHelper::start();

$nombreEquipo = $_GET['nombre'] ?? null;
if (!$nombreEquipo) {
    header("Location: equipos.php");
    exit;
}

SessionHelper::setUltimoEquipo($nombreEquipo);

$equipoDAO = new EquiposDAO();
$partidoDAO = new PartidosDAO();

$equipo = $equipoDAO->getByNombre($nombreEquipo);
if (!$equipo) {
    header("Location: equipos.php");
    exit;
}

$partidos = $partidoDAO->getByEquipo($equipo['id']);

include '../templates/header.php';
?>


<h1>Partidos de <?= htmlspecialchars($equipo['nombre']) ?></h1>
<p><strong>Estadio:</strong> <?= htmlspecialchars($equipo['estadio']) ?></p>

<?php if (empty($partidos)): ?>
    <p class="text-muted"><em>No hay partidos jugados.</em></p>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Jornada</th>
                <th>Local</th>
                <th class="text-center">Resultado</th>
                <th>Visitante</th>
                <th>Estadio</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($partidos as $p): ?>
                <tr>
                    <td><strong><?= $p['ronda'] ?></strong></td>
                    <td><?= $p['rol'] === 'local' ? '<strong>' : '' ?><?= htmlspecialchars($p['local_nombre']) ?><?= $p['rol'] === 'local' ? '</strong>' : '' ?></td>
                    <td class="text-center">
                        <?php if ($p['resultado'] !== null): ?>
                            <span class="badge bg-success fs-6"><?= $p['resultado'] ?></span>
                        <?php else: ?>
                            <em class="text-muted">Pendiente</em>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['rol'] === 'visitante' ? '<strong>' : '' ?><?= htmlspecialchars($p['visitante_nombre']) ?><?= $p['rol'] === 'visitante' ? '</strong>' : '' ?></td>
                    <td><?= htmlspecialchars($p['estadio']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


<?php include '../templates/footer.php'; ?>