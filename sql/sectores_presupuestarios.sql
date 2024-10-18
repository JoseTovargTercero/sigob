-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-10-2024 a las 01:52:14
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
-- Estructura de tabla para la tabla `sector`
--

CREATE TABLE `pl_sectores_presupuestarios` (
  `id` int(11) NOT NULL,
  `sector` varchar(10) NOT NULL,
  `programa` varchar(10) NOT NULL,
  `proyecto` varchar(10) NOT NULL,
  `nombre` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sector`
--

INSERT INTO `pl_sectores_presupuestarios` (`id`, `sector`, `programa`, `proyecto`, `nombre`) VALUES
(1, '01', '01', '00', 'Consejo Legislativo'),
(2, '01', '02', '00', 'Contraloria General del Estado'),
(3, '01', '03', '00', 'Procuraduria General'),
(4, '01', '04', '00', 'Sec. del Despacho del Gobernador y Seg.'),
(5, '01', '05', '00', 'Sec. General de Gobierno'),
(6, '01', '06', '00', 'Sec. Ejec. de Talento Humano'),
(7, '01', '07', '00', 'Sec. de Planificacion, Proyectos y PPTO.'),
(8, '01', '08', '00', 'Sec. de Administracion'),
(9, '01', '09', '00', 'Tesoreria General del Estado'),
(10, '01', '10', '00', 'Sec. Regional de Asuntos Indigenas'),
(11, '01', '11', '00', 'Unidad de Auditoria Interna'),
(12, '01', '13', '00', 'Sec. Ejec. de Bienes y Servicios'),
(13, '02', '01', '00', 'Despacho del Comandante'),
(14, '02', '02', '00', 'Sec. de Asuntos  Civiles y Politicos'),
(15, '02', '03', '00', 'Oficina de Proteccion Civil'),
(16, '02', '04', '00', 'Comandacia de Bomberos del Estado'),
(17, '06', '01', '00', 'Sec. de Turismo'),
(18, '08', '01', '00', 'Sec. promocion cultural'),
(19, '08', '02', '00', 'Sec. de Educacion jubilados penc'),
(20, '08', '03', '00', 'Sec. Ejec. para la Atencion de la Juventud'),
(21, '09', '01', '00', 'Sec. SICOAMA'),
(22, '09', '02', '00', 'Biblioteca adm'),
(23, '09', '03', '00', 'Sec. de Cultura'),
(24, '09', '04', '00', 'Tecnologia de Informacion'),
(25, '11', '01', '00', 'Sec. Ejec. de Infraestructura'),
(26, '11', '02', '00', 'SEC DE MANTENIMIENTO'),
(27, '11', '02', '02', 'Sec. Ejecutiva de Infraestructura F.C.I'),
(28, '12', '01', '02', 'Salud F.C.I'),
(29, '12', '01', '00', 'Salud contra.'),
(30, '13', '02', '00', 'Sec. Ejec. de Participacion Popular'),
(31, '13', '04', '00', 'Sec. Ejec. de Proteccion Social'),
(32, '14', '01', '00', 'Sec. Ejec. gestion humana cont'),
(33, '15', '01', '00', 'Transferencias entes'),
(34, '15', '01', '02', 'Transferencias F.C.I');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `sector`
--
ALTER TABLE `pl_sectores_presupuestarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `sector`
--
ALTER TABLE `pl_sectores_presupuestarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
