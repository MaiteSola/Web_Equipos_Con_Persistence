<?php
require_once __DIR__ . '/GenericDAO.php';

class PartidosDAO extends GenericDAO {
    const TABLE = 'partidos';

    public function getByJornada($jornada) {
        $query = "
            SELECT p.*, 
                   el.nombre as local_nombre,
                   ev.nombre as visitante_nombre
            FROM " . self::TABLE . " p
            JOIN equipos el ON p.equipo1_id = el.id
            JOIN equipos ev ON p.equipo2_id = ev.id
            WHERE p.ronda = ?
            ORDER BY p.id
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($this->conn));
        mysqli_stmt_bind_param($stmt, "i", $jornada);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $matches = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $matches;
    }

    public function getJornadas() {
        $query = "SELECT DISTINCT ronda FROM " . self::TABLE . " ORDER BY ronda";
        $result = mysqli_query($this->conn, $query);
        if (!$result) return [];
        $jornadas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $jornadas[] = (int)$row['ronda'];
        }
        return $jornadas;
    }

    public function getMaxJornada() {
        $query = "SELECT COALESCE(MAX(ronda), 0) as max FROM " . self::TABLE;
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        return (int)$row['max'];
    }

    // === EXISTE PARTIDO (ida o vuelta) ===
    /**
     * Comprueba si existe un partido entre equipo1 y equipo2.
     * @param int $equipo1Id
     * @param int $equipo2Id
     * @param int|null $ronda  Si no es null, limita la comprobación a esa ronda.
     * @param bool $soloJugados Si true, sólo considera partidos con resultado NOT NULL (ya jugados).
     * @return bool
     */
    public function existePartido($equipo1Id, $equipo2Id, $ronda = null, $soloJugados = false) {
        $query = "
            SELECT 1 FROM " . self::TABLE . " 
            WHERE (
                   (equipo1_id = ? AND equipo2_id = ?)
                OR (equipo1_id = ? AND equipo2_id = ?)
            )
        ";
        if ($ronda !== null) {
            $query .= " AND ronda = ? ";
        }
        if ($soloJugados) {
            $query .= " AND resultado IS NOT NULL ";
        }

        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) return false;

        if ($ronda !== null) {
            mysqli_stmt_bind_param($stmt, "iiiii", $equipo1Id, $equipo2Id, $equipo2Id, $equipo1Id, $ronda);
        } else {
            mysqli_stmt_bind_param($stmt, "iiii", $equipo1Id, $equipo2Id, $equipo2Id, $equipo1Id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result) !== null;
        mysqli_stmt_close($stmt);
        return $exists;
    }

    public function getByEquipo($equipoId) {
        $query = "
            SELECT 
                p.ronda,
                p.resultado,
                p.estadio,
                el.nombre as local_nombre,
                ev.nombre as visitante_nombre,
                CASE 
                    WHEN p.equipo1_id = ? THEN 'local'
                    ELSE 'visitante'
                END as rol
            FROM " . self::TABLE . " p
            JOIN equipos el ON p.equipo1_id = el.id
            JOIN equipos ev ON p.equipo2_id = ev.id
            WHERE p.equipo1_id = ? OR p.equipo2_id = ?
            ORDER BY p.ronda
        ";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($this->conn));
        
        mysqli_stmt_bind_param($stmt, "iii", $equipoId, $equipoId, $equipoId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $partidos = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $partidos;
    }

    // === CREAR PARTIDO ===
    public function crear($ronda, $equipo1Id, $equipo2Id, $resultado = null, $estadio) {
        if ($this->existePartido($equipo1Id, $equipo2Id)) {
            throw new Exception("Ya existe un partido entre estos equipos.");
        }
        if ($this->equipoOcupadoEnJornada($equipo1Id, $ronda) || $this->equipoOcupadoEnJornada($equipo2Id, $ronda)) {
            throw new Exception("Uno de los equipos ya tiene partido en esta jornada.");
        }

        $query = "INSERT INTO " . self::TABLE . " (ronda, equipo1_id, equipo2_id, resultado, estadio) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($this->conn));
        mysqli_stmt_bind_param($stmt, "iiiss", $ronda, $equipo1Id, $equipo2Id, $resultado, $estadio);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al insertar: " . mysqli_error($this->conn));
        }
        $id = mysqli_insert_id($this->conn);
        mysqli_stmt_close($stmt);
        return $id;
    }

    // === BORRAR PARTIDO ===
    public function borrar($id) {
        $query = "DELETE FROM " . self::TABLE . " WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($this->conn));
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al borrar: " . mysqli_error($this->conn));
        }
        mysqli_stmt_close($stmt);
        return true;
    }

    // === ACTUALIZAR RESULTADO (solo si NULL) ===
    public function actualizarResultado($id, $resultado) {
        $query = "UPDATE " . self::TABLE . " SET resultado = ? WHERE id = ? AND resultado IS NULL";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) throw new Exception("Prepare failed: " . mysqli_error($this->conn));
        mysqli_stmt_bind_param($stmt, "si", $resultado, $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al actualizar: " . mysqli_error($this->conn));
        }
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    // === EQUIPO OCUPADO EN JORNADA (incluso pendiente) ===
    public function equipoOcupadoEnJornada($equipoId, $jornada) {
        $query = "SELECT 1 FROM " . self::TABLE . " WHERE ronda = ? AND (equipo1_id = ? OR equipo2_id = ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "iii", $jornada, $equipoId, $equipoId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ocupado = mysqli_fetch_assoc($result) !== null;
        mysqli_stmt_close($stmt);
        return $ocupado;
    }
}
