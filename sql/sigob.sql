-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2024 a las 21:08:58
-- Versión del servidor: 10.1.36-MariaDB
-- Versión de PHP: 5.6.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nacionalidad` varchar(1) COLLATE latin1_spanish_ci NOT NULL,
  `cedula` varchar(20) COLLATE latin1_spanish_ci DEFAULT NULL,
  `cod_empleado` varchar(20) COLLATE latin1_spanish_ci DEFAULT NULL,
  `nombres` varchar(255) COLLATE latin1_spanish_ci DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `otros_años` int(11) NOT NULL DEFAULT '0',
  `status` varchar(5) COLLATE latin1_spanish_ci DEFAULT NULL,
  `obeservacion` varchar(255) COLLATE latin1_spanish_ci DEFAULT NULL,
  `cod_cargo` varchar(10) COLLATE latin1_spanish_ci NOT NULL,
  `cargo` varchar(255) COLLATE latin1_spanish_ci NOT NULL,
  `banco` varchar(255) COLLATE latin1_spanish_ci NOT NULL,
  `cuenta_bancaria` varchar(25) COLLATE latin1_spanish_ci DEFAULT NULL,
  `hijos` int(11) NOT NULL DEFAULT '0',
  `instruccion_academica` int(11) NOT NULL DEFAULT '0',
  `discapacidades` int(2) NOT NULL DEFAULT '0',
  `becas` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas`
--

CREATE TABLE `nominas` (
  `id` int(11) NOT NULL,
  `codigo` int(20) NOT NULL,
  `nombre` varchar(255) COLLATE latin1_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas_conceptos`
--

CREATE TABLE `nominas_conceptos` (
  `id` int(11) NOT NULL,
  `contador_cod_con` int(3) DEFAULT NULL,
  `cod_concepto` int(20) DEFAULT NULL,
  `nom_concepto` varchar(255) DEFAULT NULL,
  `cod_partida` varchar(255) DEFAULT NULL,
  `asignacion` varchar(50) DEFAULT NULL,
  `deduccion` varchar(50) DEFAULT NULL,
  `aportes` varchar(50) DEFAULT NULL,
  `fec_nomdes` varchar(20) DEFAULT NULL,
  `fec_nomhas` varchar(20) DEFAULT NULL,
  `cod_nomina` varchar(10) DEFAULT NULL,
  `nom_nomina` varchar(255) DEFAULT NULL,
  `tipo_concepto` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `nominas_conceptos`
--

INSERT INTO `nominas_conceptos` (`id`, `contador_cod_con`, `cod_concepto`, `nom_concepto`, `cod_partida`, `asignacion`, `deduccion`, `aportes`, `fec_nomdes`, `fec_nomhas`, `cod_nomina`, `nom_nomina`, `tipo_concepto`) VALUES
(1, 555, 1, 'SUELDO', '401-01-01-00-0000', '44798,5', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(2, 103, 15, 'PRIMA POR HIJO EMPLEADOS', '401-03-04-00-0000', '1287,5', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(3, 555, 18, 'PRIMA POR TRANSPORTE', '401-03-02-00-0000', '8959,7', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(4, 474, 19, 'PRIMA POR ANTIGUEDAD EMPLEADOS', '401-03-49-00-0000', '1720,11', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(5, 81, 27, 'PRIMA POR ESCALAFON', '401-03-98-00-0001', '6487,57', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(6, 555, 29, 'PRIMA POR FRONTERA', '401-03-97-00-0001', '8959,7', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(7, 1, 35, 'PRIMA POR ANTIGUEDAD (ESPECIAL)', '401-03-09-00-0000', '9,1', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(8, 81, 88, 'PRIMA P/DED AL S/PUBLICO UNICO DE SALUD', '401-03-98-00-0005', '973,25', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(9, 167, 147, 'PRIMA POR PROFESIONALES', '401-03-08-00-0000', '4347,42', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(10, 2, 300, 'CONTRIBUCION POR DISCAPACIDAD', '401-03-98-00-0006', '12,5', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(11, 35, 301, 'PAGO DE BECA', '401-07-18-00-0000', '337,5', '0', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'A'),
(12, 555, 501, 'S. S. O', '401-01-01-00-0000', '0', '1431,72', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'D'),
(13, 555, 502, 'RPE', '401-01-02-00-0000', '0', '357,91', '0', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'D'),
(14, 555, 560, 'A/P S.S.O', '401-06-01-00-0000', '0', '0', '2862,97', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'P'),
(15, 555, 570, 'A/P RPE', '401-06-12-00-0000', '0', '0', '1431,72', '16/03/2024', '31/03/2024', '15', 'CONTRATADOS DE LA GOBERNACION', 'P');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_users`
--

CREATE TABLE `system_users` (
  `u_id` int(11) NOT NULL,
  `u_nombre` varchar(255) NOT NULL,
  `u_oficina_id` int(11) NOT NULL,
  `u_oficina` varchar(255) DEFAULT NULL,
  `u_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish2_ci NOT NULL,
  `u_contrasena` varchar(255) NOT NULL,
  `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `system_users`
--

INSERT INTO `system_users` (`u_id`, `u_nombre`, `u_oficina_id`, `u_oficina`, `u_email`, `u_contrasena`, `creado`) VALUES
(31, 'user Nombre', 1, 'Nomina', 'corro@correo.com', '$2y$10$EyP1MOY39kuw4uREdk7ao.UUzQ10YNIZ95IZLM70MUPo5J6YzEBVG', '2024-03-07 11:18:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `nominas`
--
ALTER TABLE `nominas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `nominas_conceptos`
--
ALTER TABLE `nominas_conceptos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `system_users`
--
ALTER TABLE `system_users`
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `usuario` (`u_email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nominas`
--
ALTER TABLE `nominas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nominas_conceptos`
--
ALTER TABLE `nominas_conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `system_users`
--
ALTER TABLE `system_users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
