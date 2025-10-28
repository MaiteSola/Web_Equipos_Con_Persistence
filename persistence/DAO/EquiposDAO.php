<?php
require_once __DIR__ . '/GenericDAO.php';

class EquiposDAO extends GenericDAO {
    const TABLE = 'equipos';

    public function getAll() {
        $query = "SELECT * FROM " . self::TABLE . " ORDER BY nombre";
        $result = mysqli_query($this->conn, $query);
        if (!$result) {
            throw new Exception("Error al obtener equipos: " . mysqli_error($this->conn));
        }
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . self::TABLE . " WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $team = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $team;
    }

    public function getByNombre($nombre) {
        $query = "SELECT * FROM " . self::TABLE . " WHERE nombre = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $nombre);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $team = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $team;
    }

    public function crear($nombre, $estadio) {
        $query = "INSERT INTO " . self::TABLE . " (nombre, estadio) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "ss", $nombre, $estadio);
        
        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_error($this->conn);
            if (strpos($error, 'Duplicate entry') !== false) {
                throw new mysqli_sql_exception($error, 1062);
            }
            throw new mysqli_sql_exception("Error al ejecutar: " . $error);
        }
        
        $id = mysqli_insert_id($this->conn);
        mysqli_stmt_close($stmt);
        return $id;
    }

    // === NUEVAS FUNCIONES: BORRAR POR ID ===

public function getIdByNombre($nombre) {
    $stmt = $this->conn->prepare("SELECT id FROM " . self::TABLE . " WHERE nombre = ?");
    if (!$stmt) throw new Exception("Error prepare: " . $this->conn->error);
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['id'] : null;
}

// === BORRAR EN CASCADA POR ID ===
public function eliminarEnCascadaPorId($id) {
    $this->conn->autocommit(false);
    try {
        // 1. Borrar partidos del equipo
        $stmt = $this->conn->prepare("
            DELETE FROM partidos 
            WHERE equipo1_id = ? OR equipo2_id = ?
        ");
        if (!$stmt) throw new Exception("Error prepare partidos: " . $this->conn->error);
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $stmt->close();

        // 2. Borrar equipo
        $stmt = $this->conn->prepare("DELETE FROM equipos WHERE id = ?");
        if (!$stmt) throw new Exception("Error prepare equipo: " . $this->conn->error);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $eliminado = $stmt->affected_rows > 0;
        $stmt->close();

        $this->conn->commit();
        return $eliminado;

    } catch (Exception $e) {
        $this->conn->rollback();
        error_log("Error eliminarEnCascadaPorId($id): " . $e->getMessage());
        throw $e;
    } finally {
        $this->conn->autocommit(true);
    }
}
}