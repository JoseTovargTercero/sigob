-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-10-2024 a las 17:24:32
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
-- Estructura de tabla para la tabla `personal_directivo`
--

CREATE TABLE `personal_directivo` (
  `id` int(255) NOT NULL,
  `direccion` longtext NOT NULL,
  `nombre_apellido` longtext NOT NULL,
  `email` longtext NOT NULL,
  `telefono` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal_directivo`
--

INSERT INTO `personal_directivo` (`id`, `direccion`, `nombre_apellido`, `email`, `telefono`) VALUES
(1, 'Planificación y/o Presupuesto', 'Lic. JUAN GOMEZ', '', ''),
(2, 'Administración y/o Finanzas', 'Prof. Yenny Romero', '', ''),
(3, 'Recursos Humanos y/o Personal', 'Lic. Maria Rojas', '', ''),
(4, 'Sindico (a) Procurador (a)', 'Abog. Luis Machado', '', ''),
(5, 'Cronista del Municipio:', '', '', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `personal_directivo`
--
ALTER TABLE `personal_directivo`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `personal_directivo`
--
ALTER TABLE `personal_directivo`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
