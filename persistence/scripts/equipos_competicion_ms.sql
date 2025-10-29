-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.12.0.7122
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para competicion
CREATE DATABASE IF NOT EXISTS `competicion` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `competicion`;

-- Volcando estructura para tabla competicion.equipos
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `estadio` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla competicion.equipos: ~10 rows (aproximadamente)
INSERT INTO `equipos` (`id`, `nombre`, `estadio`) VALUES
	(1, 'Real Madrid', 'Santiago Bernabéu'),
	(2, 'FC Barcelona', 'Camp Nou'),
	(3, 'Atlético Madrid', 'Wanda Metropolitano'),
	(4, 'Valencia CF', 'Mestalla'),
	(5, 'Sevilla FC', 'Ramón Sánchez-Pizjuán'),
	(6, 'Real Betis', 'Benito Villamarín'),
	(7, 'Athletic Bilbao', 'San Mamés'),
	(8, 'Real Sociedad', 'Reale Arena'),
	(9, 'Osasuna', 'Sadar');

-- Volcando estructura para tabla competicion.partidos
CREATE TABLE IF NOT EXISTS `partidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ronda` int(11) NOT NULL,
  `equipo1_id` int(11) NOT NULL,
  `equipo2_id` int(11) NOT NULL,
  `resultado` enum('1','X','2') DEFAULT NULL,
  `estadio` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_partido` (`equipo1_id`,`equipo2_id`),
  UNIQUE KEY `unique_ronda_local` (`ronda`,`equipo1_id`),
  UNIQUE KEY `unique_ronda_visitante` (`ronda`,`equipo2_id`),
  KEY `equipo2_id` (`equipo2_id`),
  CONSTRAINT `partidos_ibfk_1` FOREIGN KEY (`equipo1_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `partidos_ibfk_2` FOREIGN KEY (`equipo2_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`equipo1_id` <> `equipo2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla competicion.partidos: ~28 rows (aproximadamente)
INSERT INTO `partidos` (`id`, `ronda`, `equipo1_id`, `equipo2_id`, `resultado`, `estadio`) VALUES
	(1, 1, 1, 2, '1', 'Santiago Bernabéu'),
	(2, 1, 3, 4, 'X', 'Wanda Metropolitano'),
	(3, 1, 5, 6, '2', 'Ramón Sánchez-Pizjuán'),
	(5, 2, 2, 3, '1', 'Camp Nou'),
	(6, 2, 4, 5, NULL, 'Mestalla'),
	(7, 2, 6, 1, 'X', 'Benito Villamarín'),
	(8, 2, 8, 7, '2', 'Reale Arena'),
	(9, 3, 1, 4, NULL, 'Santiago Bernabéu'),
	(10, 3, 2, 5, '1', 'Camp Nou'),
	(11, 3, 3, 6, '2', 'Wanda Metropolitano'),
	(12, 3, 7, 3, 'X', 'San Mamés'),
	(13, 4, 4, 2, NULL, 'Mestalla'),
	(14, 4, 5, 1, '2', 'Ramón Sánchez-Pizjuán'),
	(15, 4, 6, 8, '1', 'Benito Villamarín'),
	(16, 4, 3, 7, NULL, 'Wanda Metropolitano'),
	(17, 5, 1, 6, '1', 'Santiago Bernabéu'),
	(18, 5, 2, 7, NULL, 'Camp Nou'),
	(19, 5, 3, 8, 'X', 'Wanda Metropolitano'),
	(20, 5, 5, 4, '2', 'Ramón Sánchez-Pizjuán'),
	(21, 6, 5, 3, NULL, 'Ramón Sánchez-Pizjuán'),
	(22, 6, 6, 4, '1', 'Benito Villamarín'),
	(23, 6, 7, 1, '2', 'San Mamés'),
	(24, 6, 8, 2, 'X', 'Reale Arena'),
	(25, 7, 1, 3, '1', 'Santiago Bernabéu'),
	(26, 7, 2, 4, '1', 'Camp Nou'),
	(27, 7, 5, 7, '2', 'Ramón Sánchez-Pizjuán'),
	(28, 7, 8, 6, 'X', 'Reale Arena'),
	(34, 8, 7, 9, '1', 'San Mamés');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
