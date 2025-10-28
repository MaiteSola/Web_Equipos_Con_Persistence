<?php
require_once __DIR__.'/utils/SessionHelper.php';
SessionHelper::start();

$ultimoEquipo = SessionHelper::getUltimoEquipo();

if ($ultimoEquipo) {
    // Redirigir a partidos del equipo (ruta relativa desde index.php)
    header("Location: /Equipos_MaiteSola/app/partidos_equipo.php?nombre=" . urlencode($ultimoEquipo));
} else {
    // Redirigir a selección de equipos
    header("Location: /Equipos_MaiteSola/app/equipos.php");
}
exit;