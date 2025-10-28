<?php
require_once __DIR__.'/../persistence/DAO/EquiposDAO.php';
require_once __DIR__.'/../utils/SessionHelper.php';
SessionHelper::start();

$equipoDAO = new EquiposDAO();
$mensaje = '';

// === BORRAR POR ID ===
if (isset($_GET['borrar_id'])) {
    $id = (int)$_GET['borrar_id'];
    $equipo = $equipoDAO->getById($id);

    if ($equipo) {
        try {
            if ($equipoDAO->eliminarEnCascadaPorId($id)) {
                $_SESSION['mensaje'] = "<div class='alert alert-success'>Equipo '<strong>{$equipo['nombre']}</strong>' y sus partidos eliminados.</div>";
                SessionHelper::clearUltimoEquipo();
            } else {
                $_SESSION['mensaje'] = "<div class='alert alert-warning'>No se encontró el equipo.</div>";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $_SESSION['mensaje'] = "<div class='alert alert-warning'>Equipo no encontrado.</div>";
    }

    header("Location: equipos.php");
    exit;
}

// Mostrar mensaje (si existe)
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']);

// === AÑADIR EQUIPO ===
if ($_POST) {
    $nombre = trim($_POST['nombre']);
    $estadio = trim($_POST['estadio']);

    if ($nombre && $estadio) {
        try {
            $equipoDAO->crear($nombre, $estadio);
            $mensaje = "<div class='alert alert-success'>Equipo '<strong>$nombre</strong>' añadido.</div>";
            SessionHelper::clearUltimoEquipo();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $mensaje = "<div class='alert alert-danger'>El equipo '<strong>$nombre</strong>' ya existe.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } catch (Exception $e) {
            $mensaje = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-warning'>Completa ambos campos.</div>";
    }
}

$equipos = $equipoDAO->getAll();
include '../templates/header.php';
?>


    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 text-primary fw-bold">Equipos</h1>
        <span class="badge bg-primary fs-6"><?= count($equipos) ?> equipo<?= count($equipos) !== 1 ? 's' : '' ?></span>
    </div>

    <!-- Mensaje -->
    <?php if ($mensaje): ?>
        <?= $mensaje ?>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-4">
            <h5 class="card-title mb-3 text-muted">Añadir nuevo equipo</h5>
            <form method="POST" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="nombre" class="form-control form-control-lg" placeholder="Nombre del equipo" required>
                </div>
                <div class="col-md-5">
                    <input type="text" name="estadio" class="form-control form-control-lg" placeholder="Estadio" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
                        Añadir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de equipos -->
    <?php if (empty($equipos)): ?>
        <div class="text-center py-5">
            <p class="lead text-muted mt-3">Aún no hay equipos. ¡Añade el primero!</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($equipos as $eq): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm equipo-card position-relative">
                        
                        <!-- BOTÓN BORRAR POR ID -->
                        <a href="equipos.php?borrar_id=<?= $eq['id'] ?>" 
                           class="position-absolute top-0 end-0 m-3 text-danger z-3"
                           onclick="return confirm('¿Eliminar «<?= addslashes(htmlspecialchars($eq['nombre'])) ?>» y TODOS sus partidos?')">
                            <i class="bi bi-x-circle-fill fs-4"></i>
                        </a>

                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold text-primary mb-2">
                                <?= htmlspecialchars($eq['nombre']) ?>
                            </h5>
                            <p class="text-muted small mb-3">
                                Estadio: <strong><?= htmlspecialchars($eq['estadio']) ?></strong>
                            </p>

                            <div class="mt-auto">
                                <a href="partidos_equipo.php?nombre=<?= urlencode($eq['nombre']) ?>" 
                                   class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                    Ver Partidos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


<?php include '../templates/footer.php'; ?>