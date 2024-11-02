-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-11-2024 a las 20:18:07
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
-- Estructura de tabla para la tabla `distribucion_presupuestaria`
--

CREATE TABLE `distribucion_presupuestaria` (
  `id` int(255) NOT NULL,
  `id_partida` int(255) NOT NULL,
  `monto_inicial` varchar(255) DEFAULT NULL,
  `id_ejercicio` int(255) NOT NULL,
  `monto_actual` varchar(255) DEFAULT NULL,
  `id_sector` int(255) NOT NULL,
  `id_programa` int(255) NOT NULL,
  `id_proyecto` int(255) NOT NULL,
  `status` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `distribucion_presupuestaria`
--

INSERT INTO `distribucion_presupuestaria` (`id`, `id_partida`, `monto_inicial`, `id_ejercicio`, `monto_actual`, `id_sector`, `id_programa`, `id_proyecto`, `status`) VALUES
(9, 725, '2000', 1, '2000', 1, 1, 0, 1),
(10, 725, '1000', 1, '1000', 2, 14, 0, 1),
(11, 1436, '4000', 1, '4000', 10, 34, 0, 1),
(12, 1009, '500', 1, '500', 1, 1, 0, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `distribucion_presupuestaria`
--
ALTER TABLE `distribucion_presupuestaria`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `distribucion_presupuestaria`
--
ALTER TABLE `distribucion_presupuestaria`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
