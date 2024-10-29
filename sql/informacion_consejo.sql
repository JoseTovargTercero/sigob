-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-10-2024 a las 17:24:24
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
-- Estructura de tabla para la tabla `informacion_consejo`
--

CREATE TABLE `informacion_consejo` (
  `id` int(255) NOT NULL,
  `nombre_apellido_presidente` longtext NOT NULL,
  `nombre_apellido_secretario` longtext NOT NULL,
  `domicilio` longtext NOT NULL,
  `telefono` longtext NOT NULL,
  `pagina_web` longtext NOT NULL,
  `email` longtext NOT NULL,
  `consejo_local` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `informacion_consejo`
--

INSERT INTO `informacion_consejo` (`id`, `nombre_apellido_presidente`, `nombre_apellido_secretario`, `domicilio`, `telefono`, `pagina_web`, `email`, `consejo_local`) VALUES
(1, 'Lesgiladora: Delkis Bastidas', 'Abg. Lester Mirabal', 'Avenida Aeropuerto Sector \"Simón Bolivar\".', '', '', '', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `informacion_consejo`
--
ALTER TABLE `informacion_consejo`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `informacion_consejo`
--
ALTER TABLE `informacion_consejo`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
