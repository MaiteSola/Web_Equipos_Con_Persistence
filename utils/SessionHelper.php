<?php

/**
 * class SessionUtils
 *
 * Contains util methods to deal with SESSIONS.
 *
 * @version    0.2
 *
 * @author     Ander Frago & Miguel Goyena <miguel_goyena@cuatrovientos.org>
 */
class SessionHelper {

  /**
   * Checks if the session is not started. In that case, it calls start.
   */
  public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setUltimoEquipo($nombreEquipo) {
        $_SESSION['ultimo_equipo'] = $nombreEquipo;
    }

    public static function getUltimoEquipo() {
        return $_SESSION['ultimo_equipo'] ?? null;
    }

    public static function clearUltimoEquipo() {
        unset($_SESSION['ultimo_equipo']);
    }
}