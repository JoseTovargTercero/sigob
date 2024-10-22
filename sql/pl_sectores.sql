-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2024 a las 20:08:27
-- Versión del servidor: 10.4.16-MariaDB
-- Versión de PHP: 7.4.12

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
-- Estructura de tabla para la tabla `pl_sectores`
--

CREATE TABLE `pl_sectores` (
  `id` int(11) NOT NULL,
  `sector` varchar(11) NOT NULL,
  `denominancion` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `pl_sectores`
--

INSERT INTO `pl_sectores` (`id`, `sector`, `denominancion`) VALUES
(1, '01', 'DIRECCIÓN SUPERIOR DEL ESTADO'),
(2, '02', 'SEGURIDAD Y DEFENSA'),
(3, '06', 'TURISMO Y RECREACIÓN'),
(4, '08', 'EDUCACIÓN, CULTURA Y DEPORTES'),
(5, '09', 'CULTURA Y COMUNICACIÓN SOCIAL'),
(6, '11', 'VIVIENDA, DESARROLLO URBANO Y SERVICIOS CONEXOS'),
(7, '12', 'SALUD'),
(8, '13', 'DESARROLLO SOCIAL Y PARTICIPACIÓN'),
(9, '14', 'SEGURIDAD SOCIAL'),
(10, '15', 'GASTOS NO CLASIFICADOS SECTORIALMENTE');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pl_sectores`
--
ALTER TABLE `pl_sectores`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pl_sectores`
--
ALTER TABLE `pl_sectores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
