-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-10-2024 a las 22:54:53
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sigob`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distribucion_entes`
--

CREATE TABLE `distribucion_entes` (
  `id` int(255) NOT NULL,
  `id_ente` int(255) NOT NULL,
  `distribucion` longtext NOT NULL,
  `monto_total` varchar(255) DEFAULT NULL,
  `status` int(255) NOT NULL,
  `id_ejercicio` int(255) NOT NULL,
  `comentario` longtext NOT NULL,
  `fecha` varchar(255) DEFAULT NULL,
  `id_asignacion` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `distribucion_entes`
--

INSERT INTO `distribucion_entes` (`id`, `id_ente`, `distribucion`, `monto_total`, `status`, `id_ejercicio`, `comentario`, `fecha`, `id_asignacion`) VALUES
(1, 1, '[{\"id_partida\":\"1\",\"monto\":1000},{\"id_partida\":\"43\",\"monto\":1000}]', '2000', 1, 1, '', '2024-10-15', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `distribucion_entes`
--
ALTER TABLE `distribucion_entes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `distribucion_entes`
--
ALTER TABLE `distribucion_entes`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;