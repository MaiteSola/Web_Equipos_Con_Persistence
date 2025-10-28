<?php
require_once __DIR__ . '/../persistence/DAO/PartidosDAO.php';
require_once __DIR__ . '/../persistence/DAO/EquiposDAO.php';
require_once __DIR__ . '/../utils/SessionHelper.php';

SessionHelper::start();

$partidoDAO = new PartidosDAO();
$equipoDAO = new EquiposDAO();

// === BORRAR PARTIDO ===
if (isset($_GET['borrar'])) {
    $id = (int)$_GET['borrar'];
    $jornada = (int)($_GET['jornada'] ?? 1);

    try {
        $partidoDAO->borrar($id);
    } catch (Exception $e) {
    }

    // 🔍 Comprobar si todavía hay partidos en esa jornada
    $partidosRestantes = $partidoDAO->getByJornada($jornada);

    // Si no hay partidos, redirigir a jornada 1
    if (empty($partidosRestantes)) {
        $jornada = 1;
    }

    header("Location: partidos.php?jornada=$jornada&v=" . time());
    exit;
}

// === ACTUALIZAR RESULTADO ===
if (isset($_POST['actualizar_resultado'])) {
    $id = (int)$_POST['partido_id'];
    $resultado = $_POST['nuevo_resultado'];
    try {
        $partidoDAO->actualizarResultado($id, $resultado);
    } catch (Exception $e) {
    }
    $jornadaActual = (int)$_POST['jornada_actual'];
    header("Location: partidos.php?jornada=$jornadaActual&v=" . time());
    exit;
}

// === AÑADIR PARTIDO ===
if ($_POST && !isset($_POST['actualizar_resultado'])) {
    $equipo1Id = (int)$_POST['equipo1_id'];
    $equipo2Id = (int)$_POST['equipo2_id'];
    $resultado = $_POST['resultado'] === '' ? null : $_POST['resultado'];

    // Jornada seleccionada o nueva
    $jornada = (int)($_POST['jornada'] ?? 0);
    $maxJornada = $partidoDAO->getMaxJornada();

    if ($jornada <= 0) {
        $jornada = $maxJornada + 1; // Nueva jornada
    }
    if ($maxJornada == 0) {
        $jornada = 1;
    }

    $equipoLocal = $equipoDAO->getById($equipo1Id);
    if (!$equipoLocal) {
        $mensaje = "Error: Equipo local no encontrado.";
    } elseif ($equipo1Id === $equipo2Id) {
        $mensaje = "Error: Un equipo no puede jugar contra sí mismo.";
    } elseif ($partidoDAO->existePartido($equipo1Id, $equipo2Id, $jornada)) {
        $mensaje = "Error: Estos equipos ya tienen un partido en la jornada $jornada.";
    } elseif ($partidoDAO->equipoOcupadoEnJornada($equipo1Id, $jornada) || $partidoDAO->equipoOcupadoEnJornada($equipo2Id, $jornada)) {
        $mensaje = "Error: Uno de los equipos ya tiene partido en la jornada $jornada.";
    } else {
        try {
            $partidoDAO->crear($jornada, $equipo1Id, $equipo2Id, $resultado, $equipoLocal['estadio']);
            $mensaje = "Partido añadido en jornada $jornada.";
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }
}

// === CARGAR DATOS (siempre al final) ===
$jornadas = $partidoDAO->getJornadas();
$jornadaActual = (int)($_GET['jornada'] ?? ($jornadas[0] ?? 1));
$partidos = $partidoDAO->getByJornada($jornadaActual);
$equipos = $equipoDAO->getAll();

include '../templates/header.php';
?>


<h1 class="mb-4">Partidos</h1>

<!-- SELECTOR DE JORNADA -->
<div class="jornada-selector mb-3">
    <label class="me-2"><strong>Jornada:</strong></label>
    <select onchange="window.location='partidos.php?jornada='+this.value+'&v='+Date.now()" class="form-select d-inline w-auto">
        <?php foreach ($jornadas as $j): ?>
            <option value="<?= $j ?>" <?= $j == $jornadaActual ? 'selected' : '' ?>>Jornada <?= $j ?></option>
        <?php endforeach; ?>
        <?php if (empty($jornadas)): ?>
            <option value="1">Jornada 1</option>
        <?php endif; ?>
    </select>
</div>

<!-- MENSAJE -->
<?php if (!empty($mensaje)): ?>
    <div class="alert <?= strpos($mensaje, 'Error') === 0 ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- FORMULARIO AÑADIR -->
<h2 class="mt-4 mb-3">Añadir Partido</h2>
<form method="POST" class="row g-3 align-items-center text-end">

    <div class="col-md-2">
        <select name="jornada" class="form-select" required>
            <?php foreach ($jornadas as $j): ?>
                <option value="<?= $j ?>">Jornada <?= $j ?></option>
            <?php endforeach; ?>
            <option value="0">➕ Nueva jornada (<?= $partidoDAO->getMaxJornada() + 1 ?>)</option>
        </select>
    </div>

    <div class="col-md-2">
        <select name="equipo1_id" class="form-select" required onchange="updateEstadio()">
            <option value="">Equipo Local</option>
            <?php foreach ($equipos as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2">
        <select name="equipo2_id" class="form-select" required>
            <option value="">Equipo Visitante</option>
            <?php foreach ($equipos as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2">
        <select name="resultado" class="form-select">
            <option value="">Pendiente</option>
            <option value="1">1</option>
            <option value="X">X</option>
            <option value="2">2</option>
        </select>
    </div>

    <div class="col-md-2">
        <input type="text" id="estadio-auto" class="form-control" disabled placeholder="Estadio del local">
    </div>

    <div class="col-md-2">
        <button type="submit" class="btn btn-primary">Añadir</button>
    </div>

</form>

<!-- TABLA -->
<?php if (empty($partidos)): ?>
    <p class="text-muted"><em>No hay partidos en esta jornada.</em></p>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Local</th>
                <th class="text-center">Resultado</th>
                <th>Visitante</th>
                <th>Estadio</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($partidos as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['local_nombre']) ?></strong></td>
                    <td class="text-center">
                        <?php if ($p['resultado'] === null): ?>
                            <button type="button" class="btn btn-link p-0 text-decoration-underline text-muted"
                                data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>">
                                Pendiente
                            </button>
                        <?php else: ?>
                            <span class="badge bg-success fs-6"><?= $p['resultado'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($p['visitante_nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($p['estadio']) ?></td>
                    <td class="text-end">
                        <a href="partidos.php?borrar=<?= $p['id'] ?>&jornada=<?= $jornadaActual ?>&v=<?= time() ?>"" 
                               class=" text-danger text-decoration-none"
                            onclick="return confirm('¿Borrar este partido?')">
                            X
                        </a>
                    </td>
                </tr>

                <!-- MODAL EDITAR -->
                <?php if ($p['resultado'] === null): ?>
                    <div class="modal fade" id="editModal<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <form method="POST">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Resultado</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="partido_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="jornada_actual" value="<?= $jornadaActual ?>">
                                        <select name="nuevo_resultado" class="form-select" required>
                                            <option value="1">1</option>
                                            <option value="X">X</option>
                                            <option value="2">2</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="actualizar_resultado" class="btn btn-success btn-sm">Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>


<!-- JS: Estadio automático -->
<script>
    const estadios = <?= json_encode(array_column($equipos, 'estadio', 'id')) ?>;

    function updateEstadio() {
        const local = document.querySelector('[name="equipo1_id"]').value;
        document.getElementById('estadio-auto').value = local ? estadios[local] || '' : '';
    }
    document.querySelector('[name="equipo1_id"]').addEventListener('change', updateEstadio);
    updateEstadio();
</script>

<?php include '../templates/footer.php'; ?>