-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-10-2024 a las 16:26:45
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
-- Estructura de tabla para la tabla `informacion_personas`
--

CREATE TABLE `informacion_personas` (
  `id` int(255) NOT NULL,
  `nombres` longtext NOT NULL,
  `cargo` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `informacion_personas`
--

INSERT INTO `informacion_personas` (`id`, `nombres`, `cargo`) VALUES
(1, 'LEG. DELKIS BASTIDAS ', 'PRESIDENTA'),
(2, 'ABOG. LESTER MIRABAL', 'SECRETARIO DE CÁMARA '),
(3, 'ING. MIGUEL RODRÍGUEZ', 'GOBERNADOR DEL ESTADO AMAZONAS'),
(4, 'ING. ANALI HERRERA', 'SECRETARIA EJECUTIVA DE GOBIERNO');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `informacion_personas`
--
ALTER TABLE `informacion_personas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `informacion_personas`
--
ALTER TABLE `informacion_personas`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
