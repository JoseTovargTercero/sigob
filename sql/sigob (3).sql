-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-06-2024 a las 14:00:19
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
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id` int(11) NOT NULL,
  `prefijo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `matriz` varchar(255) NOT NULL,
  `afiliado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `bancos`
--

INSERT INTO `bancos` (`id`, `prefijo`, `nombre`, `matriz`, `afiliado`) VALUES
(2, '0175', 'Bicentenario', '0175054151515050518185', '151'),
(4, '0128', 'CARONI', '0128987979879879879856', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos_grados`
--

CREATE TABLE `cargos_grados` (
  `id` int(11) NOT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `cod_cargo` varchar(5) DEFAULT NULL,
  `grado` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cargos_grados`
--

INSERT INTO `cargos_grados` (`id`, `cargo`, `cod_cargo`, `grado`) VALUES
(1, 'ABOGADO I', '35121', '17'),
(2, 'ABOGADO II', '35122', '19'),
(3, 'ABOGADO III', '35123', '21'),
(4, 'ABOGADO IV', '35124', '23'),
(5, 'ABOGADO V', '35125', '26'),
(6, 'ADMINISTRADOR I', '12121', '17'),
(7, 'ADMINISTRADOR II', '12122', '19'),
(8, 'ADMINISTRADOR III', '12123', '21'),
(9, 'ADMINISTRADOR IV', '12124', '23'),
(10, 'ADMINISTRADOR V', '12125', '26'),
(11, 'ALMACENISTA I', '25211', '1'),
(12, 'ALMACENISTA II', '25212', '3'),
(13, 'ALMACENISTA III', '25213', '6'),
(14, 'ALMACENISTA IV', '25214', '10'),
(15, 'ANALISTA DE ORGANIZACI?N Y SISTEMAS I', '13221', '15'),
(16, 'ANALISTA DE ORGANIZACI?N Y SISTEMAS II', '13222', '17'),
(17, 'ANALISTA DE ORGANIZACI?N Y SISTEMAS III', '13223', '19'),
(18, 'ANALISTA DE ORGANIZACI?N Y SISTEMAS IV', '13224', '21'),
(19, 'ANALISTA DE ORGANIZACI?N Y SISTEMAS V', '13225', '24'),
(20, 'ANALISTA DE PERSONAL I', '15121', '17'),
(21, 'ANALISTA DE PERSONAL II', '15122', '19'),
(22, 'ANALISTA DE PERSONAL III', '15123', '21'),
(23, 'ANALISTA DE PERSONAL IV', '15124', '23'),
(24, 'ANALISTA DE PERSONAL V', '15125', '26'),
(25, 'ANALISTA DE PRESUPUESTO I', '13411', '15'),
(26, 'ANALISTA DE PRESUPUESTO II', '13412', '17'),
(27, 'ANALISTA DE PRESUPUESTO III', '13413', '19'),
(28, 'ANALISTA DE PRESUPUESTO IV', '13414', '22'),
(29, 'ANALISTA DE PRESUPUESTO V', '13415', '25'),
(30, 'ANALISTA DE PROCESAMIENTO DE DATOS I', '23451', '19'),
(31, 'ANALISTA DE PROCESAMIENTO DE DATOS II', '23452', '21'),
(32, 'ANALISTA DE PROCESAMIENTO DE DATOS III', '23453', '23'),
(33, 'ANALISTA DE PROCESAMIENTO DE DATOS IV', '23454', '24'),
(34, 'ANALISTA DE PROCESAMIENTO DE DATOS V', '23455', '26'),
(35, 'ANALISTA DE SEGURIDAD Y DEFENSA I', '85311', '17'),
(36, 'ANALISTA DE SEGURIDAD Y DEFENSA II', '85312', '20'),
(37, 'ANALISTA DE SEGURIDAD Y DEFENSA III', '85313', '22'),
(38, 'ANALISTA DE SEGURIDAD Y DEFENSA IV', '85314', '24'),
(39, 'ANALISTA DE SEGURIDAD Y DEFENSA V', '85315', '26'),
(40, 'ANALISTA FINANCIERO I', '14121', '17'),
(41, 'ANALISTA FINANCIERO II', '14122', '19'),
(42, 'ANALISTA FINANCIERO III', '14123', '21'),
(43, 'ANALISTA FINANCIERO IV', '14124', '23'),
(44, 'ANALISTA FINANCIERO V', '14125', '25'),
(45, 'ARCHIVISTA I', '22121', '1'),
(46, 'ARCHIVISTA II', '22122', '3'),
(47, 'ARCHIVISTA III', '22123', '5'),
(48, 'ARCHIVISTA IV', '22124', '8'),
(49, 'ARCHIVISTA V', '22125', '11'),
(50, 'ARQUITECTO I', '43531', '18'),
(51, 'ARQUITECTO II', '43532', '19'),
(52, 'ARQUITECTO III', '43533', '21'),
(53, 'ARQUITECTO IV', '43534', '23'),
(54, 'ARQUITECTO V', '43535', '25'),
(55, 'ASISTENTE ADMINISTRATIVO I', '12111', '1'),
(56, 'ASISTENTE ADMINISTRATIVO II', '12112', '3'),
(57, 'ASISTENTE ADMINISTRATIVO III', '12113', '15'),
(58, 'ASISTENTE ADMINISTRATIVO IV', '12114', '17'),
(59, 'ASISTENTE ADMINISTRATIVO V', '12115', '22'),
(60, 'ASISTENTE ADMINISTRATIVO VI', '12116', '24'),
(61, 'ASISTENTE EN SERVICIO SOCIAL I', '79330', '3'),
(62, 'ASISTENTE EN SERVICIO SOCIAL II', '79331', '6'),
(63, 'ASISTENTE EN SERVICIO SOCIAL III', '79332', '9'),
(64, 'ASISTENTE AGROPECUARIO I', '41131', '1'),
(65, 'ASISTENTE AGROPECUARIO II', '41132', '3'),
(66, 'ASISTENTE DE ANALISTA I', '13311', '1'),
(67, 'ASISTENTE DE ANALISTA II', '13312', '3'),
(68, 'ASISTENTE DE ANALISTA III', '13313', '15'),
(69, 'ASISTENTE DE ASUNTOS LEGALES I', '35111', '3'),
(70, 'ASISTENTE DE ASUNTOS LEGALES II', '35112', '5'),
(71, 'ASISTENTE DE BIBLIOTECA I', '36621', '1'),
(72, 'ASISTENTE DE BIBLIOTECA II', '36622', '2'),
(73, 'ASISTENTE DE BIBLIOTECA III', '36623', '15'),
(74, 'ASISTENTE DE INGENIERIA I', '43480', '2'),
(75, 'ASISTENTE DE INGENIERIA II', '43481', '5'),
(76, 'ASISTENTE DE LABORATORIO CLINICO I', '72111', '1'),
(77, 'ASISTENTE DE LABORATORIO CLINICO II', '72112', '3'),
(78, 'ASISTENTE DE LABORATORIO CLINICO III', '72113', '6'),
(79, 'ASISTENTE DE OFICINA I', '22211', '1'),
(80, 'ASISTENTE DE OFICINA II', '22212', '3'),
(81, 'ASISTENTE DE OFICINA III', '22213', '6'),
(82, 'ASISTENTE DE PRIMEROS AUXILIOS I', '85611', '1'),
(83, 'ASISTENTE DE PRIMEROS AUXILIOS II', '85612', '15'),
(84, 'ASISTENTE DE RAYOS X', '72430', '1'),
(85, 'ASISTENTE DE AEROPUERTO I', '51321', '1'),
(86, 'ASISTENTE DE AEROPUERTO II', '51322', '3'),
(87, 'ASISTENTE DE AEROPUERTO III', '51323', '15'),
(88, 'AUDITOR I', '21211', '17'),
(89, 'AUDITOR II', '21212', '19'),
(90, 'AUDITOR III', '21213', '21'),
(91, 'AUDITOR IV', '21214', '24'),
(92, 'AUDITOR V', '21215', '26'),
(93, 'ASISTENTE DE PROTOCOLO I', '33110', '5'),
(94, 'ASISTENTE DE PROTOCOLO II', '33111', '8'),
(95, 'ASISTENTE DE PROTOCOLO III', '33112', '11'),
(96, 'ASISTENTE DE RELACIONES PUBLICAS I', '31330', '15'),
(97, 'ASISTENTE DE RELACIONES PUBLICAS II', '31331', '17'),
(98, 'ASISTENTE DE RELACIONES PUBLICAS III', '31332', '19'),
(99, 'ASISTENTE DE RELACIONES PUBLICAS IV', '31333', '21'),
(100, 'ASISTENTE DE RELACIONES PUBLICAS V', '31334', '24'),
(101, 'ASISTENTE DE TERAPIA', '72510', '1'),
(102, 'ASISTENTE DE HIDROMETEOROLOGIA I', '43621', '3'),
(103, 'ASISTENTE DE HIDROMETEOROLOGIA II', '43622', '5'),
(104, 'ASISTENTE DE HIDROMETEOROLOGIA III', '43623', '7'),
(105, 'BIBLIOTECOLOGO I', '36631', '17'),
(106, 'BIBLIOTECOLOGO II', '36632', '19'),
(107, 'BIBLIOTECOLOGO III', '36633', '21'),
(108, 'BIBLIOTECOLOGO IV', '36634', '24'),
(109, 'BIBLIOTECOLOGO V', '36635', '26'),
(110, 'BIOANALISTA I', '72221', '17'),
(111, 'BIOANALISTA II', '72222', '19'),
(112, 'BIOANALISTA III', '72223', '21'),
(113, 'BIOANALISTA IV', '72224', '24'),
(114, 'BIOANALISTA V', '72225', '26'),
(115, 'BI?LOGO I', '42521', '17'),
(116, 'BI?LOGO II', '42522', '19'),
(117, 'BI?LOGO III', '42523', '21'),
(118, 'BI?LOGO IV', '42524', '24'),
(119, 'BI?LOGO V', '42525', '26'),
(120, 'CAJERO I', '21511', '1'),
(121, 'CAJERO II', '21512', '3'),
(122, 'CAJERO III', '21513', '4'),
(123, 'CAJERO IV', '21514', '9'),
(124, 'COMPRADOR I', '25311', '1'),
(125, 'COMPRADOR II', '25312', '3'),
(126, 'COMPRADOR III', '25313', '15'),
(127, 'COMUNICADOR SOCIAL I', '31321', '17'),
(128, 'COMUNICADOR SOCIAL II', '31322', '19'),
(129, 'COMUNICADOR SOCIAL III', '31323', '21'),
(130, 'COMUNICADOR SOCIAL IV', '31324', '24'),
(131, 'COMUNICADOR SOCIAL V', '31325', '26'),
(132, 'CONTABILISTA I', '21111', '1'),
(133, 'CONTABILISTA II', '21112', '3'),
(134, 'CONTABILISTA III', '21113', '15'),
(135, 'CONTABILISTA IV', '21114', '16'),
(136, 'CONTADOR I', '21131', '17'),
(137, 'CONTADOR II', '21132', '19'),
(138, 'CONTADOR III', '21133', '21'),
(139, 'CONTADOR IV', '21134', '24'),
(140, 'CONTADOR V', '21135', '26'),
(141, 'DIBUJANTE I', '43721', '1'),
(142, 'DIBUJANTE II', '43722', '3'),
(143, 'DIBUJANTE III', '43723', '5'),
(144, 'DIETISTA I', '77121', '17'),
(145, 'DIETISTA II', '77122', '19'),
(146, 'DIETISTA III', '77123', '21'),
(147, 'DIETISTA IV', '77124', '24'),
(148, 'DIETISTA V', '77125', '26'),
(149, 'ECONOMA I', '77141', '1'),
(150, 'ECONOMA II', '77142', '3'),
(151, 'ECONOMISTA I', '36221', '17'),
(152, 'ECONOMISTA II', '36222', '19'),
(153, 'ECONOMISTA III', '36223', '21'),
(154, 'ECONOMISTA IV', '36224', '24'),
(155, 'ECONOMISTA V', '36225', '26'),
(156, 'ENFERMERA DE SALUD PUBLICA I', '71353', '16'),
(157, 'ENFERMERA DE SALUD PUBLICA II', '71354', '17'),
(158, 'ENFERMERA DE SALUD PUBLICA III', '71355', '19'),
(159, 'ENFERMERA DE SALUD PUBLICA IV', '71356', '22'),
(160, 'ENFERMERA DE SALUD PUBLICA V', '71357', '25'),
(161, 'ENTRENADOR DEPORTIVO I', '34231', '1'),
(162, 'ENTRENADOR DEPORTIVO II', '34232', '2'),
(163, 'ENTRENADOR DEPORTIVO III', '34233', '15'),
(164, 'ENTRENADOR DEPORTIVO IV', '34234', '17'),
(165, 'ENTRENADOR DEPORTIVO V', '34235', '21'),
(166, 'ENTRENADOR DEPORTIVO VI', '34236', '24'),
(167, 'FARMACEUTICO I', '74211', '17'),
(168, 'FARMACEUTICO II', '74212', '19'),
(169, 'FARMACEUTICO III', '74213', '21'),
(170, 'FARMACEUTICO IV', '74214', '24'),
(171, 'FARMACEUTICO V', '74215', '26'),
(172, 'FISCAL DE RENTAS I', '21321', '16'),
(173, 'FISCAL DE RENTAS II', '21322', '18'),
(174, 'FISCAL DE RENTAS III', '21323', '20'),
(175, 'FISCAL DE RENTAS IV', '21324', '22'),
(176, 'FISCAL DE RENTAS V', '21325', '24'),
(177, 'FOTOGRAFO I', '45251', '1'),
(178, 'FOTOGRAFO II', '45252', '3'),
(179, 'FOTOGRAFO III', '45253', '17'),
(180, 'FISIOTERAPEUTA I', '72531', '15'),
(181, 'FISIOTERAPEUTA II', '72532', '17'),
(182, 'FISIOTERAPEUTA III', '72533', '19'),
(183, 'FISIOTERAPEUTA IV', '72534', '22'),
(184, 'FISIOTERAPEUTA V', '72535', '24'),
(185, 'GESTOR DE AEROPUERTO I', '51331', '15'),
(186, 'GESTOR DE AEROPUERTO II', '51332', '19'),
(187, 'GESTOR DE AEROPUERTO III', '51333', '21'),
(188, 'GESTOR DE SERVICIOS TURISTICOS I', '31581', '20'),
(189, 'GESTOR DE SERVICIOS TURISTICOS II', '31582', '22'),
(190, 'GESTOR DE SERVICIOS TURISTICOS III', '31583', '24'),
(191, 'GE?LOGO I', '44341', '18'),
(192, 'GE?LOGO II', '44342', '20'),
(193, 'GE?LOGO III', '44343', '22'),
(194, 'GE?LOGO IV', '44344', '24'),
(195, 'GE?LOGO V', '44345', '26'),
(196, 'HIGIENISTA DENTAL I', '73121', '1'),
(197, 'HIGIENISTA DENTAL II', '73122', '4'),
(198, 'INFORMADOR TURISTICO I', '31521', '3'),
(199, 'INFORMADOR TURISTICO II', '31522', '15'),
(200, 'INFORMADOR TURISTICO III', '31523', '16'),
(201, 'INFORMADOR TURISTICO IV', '31524', '17'),
(202, 'INFORMADOR TURISTICO V', '31525', '21'),
(203, 'INFORMADOR TURISTICO VI', '31526', '24'),
(204, 'INGENIERO AGRONOMO I', '41151', '18'),
(205, 'INGENIERO AGRONOMO II', '41152', '20'),
(206, 'INGENIERO AGRONOMO III', '41153', '22'),
(207, 'INGENIERO AGRONOMO IV', '41154', '24'),
(208, 'INGENIERO AGRONOMO V', '41155', '26'),
(209, 'INGENIERO CIVIL I', '43421', '18'),
(210, 'INGENIERO CIVIL II', '43422', '20'),
(211, 'INGENIERO CIVIL III', '43423', '22'),
(212, 'INGENIERO CIVIL IV', '43424', '24'),
(213, 'INGENIERO CIVIL V', '43425', '26'),
(214, 'INGENIERO DE MINAS I', '44411', '18'),
(215, 'INGENIERO DE MINAS II', '44412', '20'),
(216, 'INGENIERO DE MINAS III', '44413', '22'),
(217, 'INGENIERO DE MINAS IV', '44414', '24'),
(218, 'INGENIERO DE MINAS V', '44415', '26'),
(219, 'INGENIERO ELECTRICISTA I', '46511', '18'),
(220, 'INGENIERO ELECTRICISTA II', '46512', '20'),
(221, 'INGENIERO ELECTRICISTA III', '46513', '22'),
(222, 'INGENIERO ELECTRICISTA IV', '46514', '24'),
(223, 'INGENIERO ELECTRICISTA V', '46515', '26'),
(224, 'INGENIERO HIDROMETEOROLOGISTA I', '43641', '18'),
(225, 'INGENIERO HIDROMETEOROLOGISTA II', '43642', '20'),
(226, 'INGENIERO HIDROMETEOROLOGISTA III', '46643', '22'),
(227, 'INGENIERO HIDROMETEOROLOGISTA IV', '46644', '24'),
(228, 'INGENIERO HIDROMETEOROLOGISTA V', '46645', '26'),
(229, 'INGENIERO MECANICO I', '46311', '18'),
(230, 'INGENIERO MECANICO II', '46312', '20'),
(231, 'INGENIERO MECANICO III', '46313', '22'),
(232, 'INGENIERO MECANICO IV', '46314', '24'),
(233, 'INGENIERO MECANICO V', '46315', '26'),
(234, 'INGENIERO PETROLERO I', '44521', '18'),
(235, 'INGENIERO PETROLERO II', '44522', '20'),
(236, 'INGENIERO PETROLERO III', '44523', '22'),
(237, 'INGENIERO QUIMICO I', '44741', '18'),
(238, 'INGENIERO QUIMICO II', '44742', '21'),
(239, 'INGENIERO SANITARIO I', '43441', '18'),
(240, 'INGENIERO SANITARIO II', '43442', '20'),
(241, 'INGENIERO SANITARIO III', '43443', '22'),
(242, 'INGENIERO SANITARIO IV', '43444', '24'),
(243, 'INGENIERO SANITARIO V', '43445', '26'),
(244, 'INSPECTOR AUXILIAR DE OBRAS DE INGENIERIA', '43460', '2'),
(245, 'INSPECTOR DE OBRAS DE INGENIERIA I', '43471', '15'),
(246, 'INSPECTOR DE OBRAS DE INGENIERIA II', '43472', '16'),
(247, 'INSPECTOR DE OBRAS DE INGENIERIA III', '43473', '20'),
(248, 'INSPECTOR DE OBRAS DE INGENIERIA IV', '43474', '22'),
(249, 'INSPECTOR DE OBRAS DE INGENIERIA V', '43475', '24'),
(250, 'INSPECTOR DE RENTAS I', '21311', '22'),
(251, 'INSPECTOR DE RENTAS II', '21312', '24'),
(252, 'INSPECTOR DE SALUD P?BLICA I', '76231', '4'),
(253, 'INSPECTOR DE SALUD P?BLICA II', '76232', '15'),
(254, 'INSPECTOR DE SALUD P?BLICA III', '76233', '16'),
(255, 'INSPECTOR TURISTICO I', '31561', '1'),
(256, 'INSPECTOR TURISTICO II', '31562', '15'),
(257, 'INSPECTOR TURISTICO III', '31563', '16'),
(258, 'INSPECTOR TURISTICO IV', '31564', '20'),
(259, 'INSPECTOR TURISTICO V', '31565', '22'),
(260, 'INSPECTOR TURISTICO VI', '31566', '24'),
(261, 'LABORATORISTA I', '45121', '1'),
(262, 'LABORATORISTA II', '45122', '3'),
(263, 'LABORATORISTA III', '45123', '15'),
(264, 'LOCUTOR I', '32141', '3'),
(265, 'LOCUTOR II', '32142', '5'),
(266, 'LOCUTOR III', '32143', '7'),
(267, 'LIQUIDADOR I', '21341', '15'),
(268, 'LIQUIDADOR II', '21342', '17'),
(269, 'LIQUIDADOR III', '21343', '19'),
(270, 'MEDICO I', '75131', '18'),
(271, 'MEDICO II', '75132', '20'),
(272, 'MEDICO III', '75133', '23'),
(273, 'MEDICO ESPECIALISTA I', '75311', '19'),
(274, 'MEDICO ESPECIALISTA II', '75312', '21'),
(275, 'MEDICO ESPECIALISTA III', '75313', '23'),
(276, 'MEDICO ESPECIALISTA IV', '75314', '25'),
(277, 'MEDICO ESPECIALISTA V', '75315', '26'),
(278, 'MEDICO VETERINARIO I', '78211', '18'),
(279, 'MEDICO VETERINARIO II', '78212', '20'),
(280, 'MEDICO VETERINARIO III', '78213', '22'),
(281, 'MEDICO VETERINARIO IV', '78214', '24'),
(282, 'MEDICO VETERINARIO V', '78215', '26'),
(283, 'ODONTOLOGO I', '73211', '17'),
(284, 'ODONTOLOGO II', '73212', '19'),
(285, 'ODONTOLOGO III', '73213', '21'),
(286, 'ODONTOLOGO IV', '73214', '23'),
(287, 'ODONTOLOGO V', '73215', '26'),
(288, 'OFICIAL DE BUSQUEDA Y SALVAMENTO I', '85511', '11'),
(289, 'OFICIAL DE BUSQUEDA Y SALVAMENTO II', '85512', '12'),
(290, 'OFICIAL DE BUSQUEDA Y SALVAMENTO III', '85513', '13'),
(291, 'OFICIAL DE BUSQUEDA Y SALVAMENTO IV', '85514', '14'),
(292, 'OPERADOR DE EQUIPOS DE COMPUTACION I', '23331', '1'),
(293, 'OPERADOR DE EQUIPOS DE COMPUTACION II', '23332', '3'),
(294, 'OPERADOR DE EQUIPOS DE COMPUTACION III', '23333', '16'),
(295, 'OPERADOR DE EQUIPOS DE COMPUTACION IV', '23334', '18'),
(296, 'OPERADOR DE EQUIPOS DE COMPUTACION V', '23335', '22'),
(297, 'OPERADOR DE EQUIPOS DE COMPUTACION VI', '23336', '24'),
(298, 'OPERADOR DE TELECOMUNICACIONES I', '51411', '1'),
(299, 'OPERADOR DE TELECOMUNICACIONES II', '51412', '3'),
(300, 'OPERADOR DE TELECOMUNICACIONES III', '51413', '5'),
(301, 'OPERADOR DE TELECOMUNICACIONES IV', '51414', '7'),
(302, 'OPERADOR DE TELECOMUNICACIONES V', '51415', '11'),
(303, 'PISCICULTOR I', '42138', '4'),
(304, 'PISCICULTOR II', '42139', '7'),
(305, 'PLANIFICADOR I', '13361', '17'),
(306, 'PLANIFICADOR II', '13362', '19'),
(307, 'PLANIFICADOR III', '13363', '21'),
(308, 'PLANIFICADOR IV', '13364', '23'),
(309, 'PLANIFICADOR V', '13365', '26'),
(310, 'PROGRAMADOR I', '23421', '4'),
(311, 'PROGRAMADOR II', '23422', '17'),
(312, 'PROGRAMADOR III', '23423', '19'),
(313, 'PROGRAMADOR IV', '23424', '21'),
(314, 'PROGRAMADOR V', '23425', '24'),
(315, 'PROMOTOR CULTURAL I', '37921', '17'),
(316, 'PROMOTOR CULTURAL II', '37922', '19'),
(317, 'PROMOTOR CULTURAL III', '37923', '21'),
(318, 'PROMOTOR CULTURAL IV', '37924', '23'),
(319, 'PROMOTOR CULTURAL V', '37925', '26'),
(320, 'PROMOTOR DE BIENESTAR SOCIAL I', '79370', '3'),
(321, 'PROMOTOR DE BIENESTAR SOCIAL II', '79371', '5'),
(322, 'PROMOTOR DE BIENESTAR SOCIAL III', '79372', '8'),
(323, 'PROMOTOR TURISTICO I', '31541', '1'),
(324, 'PROMOTOR TURISTICO II', '31542', '15'),
(325, 'PROMOTOR TURISTICO III', '31543', '16'),
(326, 'PROMOTOR TURISTICO IV', '31544', '19'),
(327, 'PROMOTOR TURISTICO V', '31545', '21'),
(328, 'PROMOTOR TURISTICO VI', '31546', '24'),
(329, 'PSIC?LOGO I', '36321', '17'),
(330, 'PSIC?LOGO II', '36322', '19'),
(331, 'PSIC?LOGO III', '36323', '21'),
(332, 'PSIC?LOGO IV', '36324', '24'),
(333, 'PSIC?LOGO V', '36325', '26'),
(334, 'PUBLICISTA I', '31351', '15'),
(335, 'PUBLICISTA II', '31352', '17'),
(336, 'PUBLICISTA III', '31353', '19'),
(337, 'PUBLICISTA IV', '31354', '21'),
(338, 'PUBLICISTA V', '31355', '24'),
(339, 'REGISTRADOR DE BIENES Y MATERIAS I', '25111', '1'),
(340, 'REGISTRADOR DE BIENES Y MATERIAS II', '25112', '2'),
(341, 'REGISTRADOR DE BIENES Y MATERIAS III', '25113', '15'),
(342, 'REGISTRADOR DE BIENES Y MATERIAS IV', '25114', '17'),
(343, 'REGISTRADOR DE BIENES Y MATERIAS V', '25115', '21'),
(344, 'REGISTRADOR DE BIENES Y MATERIAS VI', '25116', '24'),
(345, 'SECRETARIA I', '24311', '1'),
(346, 'SECRETARIA II', '24312', '3'),
(347, 'SECRETARIA III', '24313', '5'),
(348, 'SECRETARIA IV', '24314', '8'),
(349, 'SECRETARIA V', '24315', '11'),
(350, 'SECRETARIA EJECUTIVA I', '24341', '7'),
(351, 'SECRETARIA EJECUTIVA II', '24342', '9'),
(352, 'SECRETARIA EJECUTIVA III', '24343', '11'),
(353, 'SECRETARIA EJECUTIVA IV', '24344', '13'),
(354, 'SECRETARIA EJECUTIVA V', '24345', '14'),
(355, 'SUPERVISOR DE ALMACEN I', '25220', '16'),
(356, 'SUPERVISOR DE ALMACEN II', '25221', '17'),
(357, 'SUPERVISOR DE SERVICIOS GENERALES DE', '22411', '3'),
(358, 'PERSONAL I', '', ''),
(359, 'SUPERVISOR DE SERVICIOS GENERALES DE', '22412', '5'),
(360, 'PERSONAL II', '', ''),
(361, 'SUPERVISOR DE SERVICIOS GENERALES DE', '22413', '18'),
(362, 'PERSONAL III', '', ''),
(363, 'TECNICO AGROPECUARIO I', '41111', '2'),
(364, 'TECNICO AGROPECUARIO II', '41112', '3'),
(365, 'TECNICO AGROPECUARIO III', '41113', '15'),
(366, 'TECNICO AGROPECUARIO IV', '41114', '16'),
(367, 'TECNICO DE EQUIPOS MEDICOS I', '72411', '15'),
(368, 'TECNICO DE EQUIPOS MEDICOS II', '72412', '17'),
(369, 'TECNICO DE EQUIPOS MEDICOS III', '72413', '19'),
(370, 'TECNICO DE EQUIPOS MEDICOS IV', '72414', '22'),
(371, 'TECNICO DE EQUIPOS MEDICOS V', '72415', '25'),
(372, 'TECNICO RADI?LOGO I', '72441', '15'),
(373, 'TECNICO RADI?LOGO II', '72442', '16'),
(374, 'TECNICO SUPERIOR EN TRABAJO SOCIAL I', '79341', '15'),
(375, 'TECNICO SUPERIOR EN TRABAJO SOCIAL II', '79342', '16'),
(376, 'TRABAJADOR SOCIAL I', '79351', '17'),
(377, 'TRABAJADOR SOCIAL II', '79352', '19'),
(378, 'TRABAJADOR SOCIAL III', '79353', '21'),
(379, 'TRABAJADOR SOCIAL IV', '79534', '23'),
(380, 'TRABAJADOR SOCIAL V', '79355', '26'),
(381, 'TOPOGRAFO I', '43121', '15'),
(382, 'TOPOGRAFO II', '43122', '16'),
(383, 'ASEADORA (Bedel)', '7102', '1'),
(384, 'OBRERO', '7102', '1'),
(385, 'AUXILIAR DE LABORATORIO', '7104', '1'),
(386, 'CAMARERA', '7104', '1'),
(387, 'ASEADOR DE AREAS PUBLICAS', '7107', '1'),
(388, 'AYUDANTE DE COSTURA', '7103', '1'),
(389, 'AYUDANTE DE SERVICIOS DE COCINA', '2127', '1'),
(390, 'PORTERO', '3128', '2'),
(391, 'MENSAJERO', '7129', '2'),
(392, 'DEPOSITARIO', '7135', '2'),
(393, 'CUIDADOR DE ?REAS VERDES', '7133', '2'),
(394, 'OPERADOR DE COMPAGINACION Y ENCUADERNACION', '4125', '2'),
(395, 'AYUDANTE DE MAQUINARIA', '5125', '2'),
(396, 'CERRAJERO', '7127', '2'),
(397, 'LAVANDERO', '7128', '2'),
(398, 'MENSAJERO MOTORIZADO', '7155', '3'),
(399, 'JARDINERO', '7153', '3'),
(400, 'AYUDANTE DE MECANIZA EN GENERAL', '5155', '3'),
(401, 'AYUDANTE DE SERVICIOS GENERALES', '7152', '3'),
(402, 'AYUDANTE AGROPECUARIO', '1151', '3'),
(403, 'OPERADOR DE MAQUINA Y FOTOCOPIADORA', '4151', '3'),
(404, 'AYUDANTE DE MANTENIMIENTO DE VIAS TERRESTRES', '5152', '3'),
(405, 'FUMIGADOR', '6150', '3'),
(406, 'ASEADOR DE MATERIALES DE LABORATORIO', '6151', '3'),
(407, 'OPERADOR DE MAQUINAS DE OFICINAS', '7157', '3'),
(408, 'OPERADOR DE SONIDO', '7160', '3'),
(409, 'CAMILLERO', '6174', '4'),
(410, 'RECEPTOR INFORMADOR', '3176', '4'),
(411, 'CHOFER', '7174', '4'),
(412, 'PINTOR', '7177', '4'),
(413, 'LATONERO Y PINTOR', '5174', '4'),
(414, 'MEC?NICO DE MOTO', '5176', '4'),
(415, 'COSTURERO (A)', '7204', '5'),
(416, 'COCINERO (A)', '2202', '5'),
(417, 'VIGILANTE', '8201', '5'),
(418, 'OPERADOR DE MAQUINAS LIVIANAS', '5202', '5'),
(419, 'PLOMERO', '7212', '5'),
(420, 'AUXILIAR DE SERVICIOS DE OFICINA', '7202', '5'),
(421, 'AYUDANTE DE ALMAC?N', '7201', '5'),
(422, 'POLICIA ESCOLAR', '1537', '5'),
(423, 'HERRERO', '7208', '5'),
(424, 'CHOFER DE TRANSPORTE', '7206', '5'),
(425, 'MAQUINISTA', '5201', '5'),
(426, 'MECANICO DE IMPRENTA', '5204', '5'),
(427, 'MECANICO DE MAQUINARIAS INDUSTRIALES', '5205', '5'),
(428, 'AUXILIAR DE FARMACIA', '6201', '5'),
(429, 'AUXILIAR DE TELECOMUNICACIONES', '7203', '5'),
(430, 'TAPICERO', '7213', '5'),
(431, 'UTILERO', '7218', '5'),
(432, 'CHOFER DE CARGA', '7231', '5'),
(433, 'MEC?NICO DE REFRIGERACI?N', '5226', '6'),
(434, 'MEC?NICO AUTOMOTRIZ', '5225', '6'),
(435, 'AUXILIAR DE MEDICINA SIMPLIFICADA', '6228', '6'),
(436, 'AUXILIAR DE RAYOS X', '6229', '6'),
(437, 'ALBAÑIL', '7225', '6'),
(438, 'CARPINTERO', '7227', '6'),
(439, 'SOLDADOR', '7229', '6'),
(440, 'ELECTRICISTA', '3161', '6'),
(441, 'OPERADOR DE ACUEDUCTO', '5227', '6'),
(442, 'OPERADOR DE MAQUINARIAS PESADAS', '5229', '6'),
(443, 'OPERADOR DE PLANTAS HIDROEL?CTRICAS', '5230', '6'),
(444, 'AUXILIAR DE AUTOPSIA', '6174', '6'),
(445, 'AUXILIAR DE LABORATORIO', '6226', '6'),
(446, 'AUXILIAR DE TERAPIA', '6230', '6'),
(447, 'AYUDANTE DE TOPOGRAF?A', '7226', '6'),
(448, 'MOTORISTA', '5252', '7'),
(449, 'MECANICO DE EQUIPOS M?DICOS', '5253', '7'),
(450, 'INSPECTOR AUXILIAR DE OBRAS', '7253', '7'),
(451, 'ESCOLTA', '8250', '7'),
(452, 'AUXILIAR DE ENFERMER?A (ENFERMERO) (A)', '6274', '8'),
(453, 'CAPORAL', '1279', '8'),
(454, 'MECANICO DE MOTORES DIESEL', '5274', '8'),
(455, 'MECANICO DE AVIACION', '5275', '8'),
(456, 'ELECTRICISTA DE ALTA TENSI?N', '7274', '8'),
(457, 'ELECTROMECANICO', '7275', '8'),
(458, 'OBRERO OFICIAL', '334', '9'),
(459, 'SUPERVISOR DE MANTENIMIENTO DE ?REAS P?BLICA', '7305', '10'),
(460, 'SUPERVISOR DE SERVICIOS INTERNOS', '7303', '10'),
(461, 'SUPERVISOR DE PLANTA HIDROEL?CTRICA', '5301', '10'),
(462, 'SUPERVISOR DE TRANSPORTE', '7606', '10'),
(463, 'SUPERVISOR DE SERVICIOS DE SEGURIDAD', '8350', '10'),
(464, 'SUPERVISOR DE SERVICIOS ESPECIALES', '8350', '10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conceptos`
--

CREATE TABLE `conceptos` (
  `id` int(11) NOT NULL,
  `nom_concepto` varchar(255) DEFAULT NULL,
  `cod_partida` varchar(255) DEFAULT NULL,
  `tipo_concepto` varchar(2) DEFAULT NULL,
  `tipo_calculo` int(1) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `conceptos`
--

INSERT INTO `conceptos` (`id`, `nom_concepto`, `cod_partida`, `tipo_concepto`, `tipo_calculo`, `valor`) VALUES
(21, 'CONTRIBUCION POR DISCAPACIDAD', '4.01.03.40.00', 'A', 6, '5'),
(24, 'PRIMA POR HIJO EMPLEADOS', '4.01.03.04.00', 'A', 1, '5'),
(25, 'PRIMA POR TRANSPORTE', '4.01.04.09.00', 'A', 1, '50'),
(26, 'PRIMA POR ANTIGUEDAD EMPLEADOS', '4.01.03.09.00', 'A', 1, '10'),
(27, 'PRIMA POR ESCALAFON', '4.01.02.00.00', 'A', 1, '5'),
(28, 'PRIMA POR FRONTERA', '4.01.03.30.00', 'A', 1, '5'),
(29, 'PRIMA POR PROFESIONALES', '4.01.03.08.00', 'A', 1, '20'),
(30, 'S. S. O', '3.12.02.01.00', 'D', 1, '5'),
(31, 'RPE', '3.12.02.10.00', 'D', 1, '5'),
(32, 'A/P S.S.O', '4.01.06.25.00', 'P', 1, '5'),
(33, 'A/P RPE', '4.01.06.19.00', 'P', 1, '5'),
(34, 'PAGO DE BECA', '4.01.07.02.00', 'A', 1, '10'),
(35, 'PRIMA P/DED AL S/PUBLICO UNICO DE SALUD', '4.01.07.08.00', 'A', 1, '5'),
(36, 'PRIMA POR ANTIGUEDAD (ESPECIAL)', '4.01.03.09.00', 'A', 1, '10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conceptos_aplicados`
--

CREATE TABLE `conceptos_aplicados` (
  `id` int(255) NOT NULL,
  `concepto_id` varchar(255) NOT NULL,
  `nom_concepto` varchar(255) NOT NULL,
  `fecha_aplicar` varchar(255) NOT NULL,
  `tipo_calculo` varchar(255) NOT NULL,
  `n_conceptos` varchar(255) NOT NULL,
  `emp_cantidad` varchar(255) NOT NULL,
  `tabulador` varchar(255) NOT NULL,
  `empleados` varchar(255) NOT NULL,
  `nombre_nomina` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conceptos_aplicados`
--

INSERT INTO `conceptos_aplicados` (`id`, `concepto_id`, `nom_concepto`, `fecha_aplicar`, `tipo_calculo`, `n_conceptos`, `emp_cantidad`, `tabulador`, `empleados`, `nombre_nomina`) VALUES
(21, 'sueldo_base', 'Sueldo Base', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '', '[]', '3', '32', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(22, '21', 'CONTRIBUCION POR DISCAPACIDAD', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '6', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(23, '24', 'PRIMA POR HIJO EMPLEADOS', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(24, '25', 'PRIMA POR TRANSPORTE', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(25, '26', 'PRIMA POR ANTIGUEDAD EMPLEADOS', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(26, '27', 'PRIMA POR ESCALAFON', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[\"24\",\"25\",\"26\"]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(27, '28', 'PRIMA POR FRONTERA', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(28, '29', 'PRIMA POR PROFESIONALES', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(29, '30', 'S. S. O', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(30, '31', 'RPE', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(31, '32', 'A/P S.S.O', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(32, '33', 'A/P RPE', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(33, '34', 'PAGO DE BECA', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros'),
(34, '35', 'PRIMA P/DED AL S/PUBLICO UNICO DE SALUD', '[\"s1\",\"s2\",\"s3\",\"s4\"]', '1', '[]', '3', '', '[\"27\",\"28\",\"29\",\"30\"]', 'Obreros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conceptos_formulacion`
--

CREATE TABLE `conceptos_formulacion` (
  `id` int(11) NOT NULL,
  `tipo_calculo` varchar(10) NOT NULL,
  `condicion` varchar(255) NOT NULL,
  `valor` varchar(50) NOT NULL,
  `concepto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `conceptos_formulacion`
--

INSERT INTO `conceptos_formulacion` (`id`, `tipo_calculo`, `condicion`, `valor`, `concepto_id`) VALUES
(4, '1', 'hijos=\'3\'', '10', 21);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dependencias`
--

CREATE TABLE `dependencias` (
  `id_dependencia` int(255) NOT NULL,
  `dependencia` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dependencias`
--

INSERT INTO `dependencias` (`id_dependencia`, `dependencia`) VALUES
(6, 'GOBERNACION'),
(7, 'ESCUELA'),
(9, 'Alcaldia'),
(10, 'Barrio'),
(11, 'Adentro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nacionalidad` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `cod_empleado` varchar(20) DEFAULT NULL,
  `nombres` varchar(255) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `otros_años` int(11) NOT NULL DEFAULT 0,
  `status` varchar(5) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `cod_cargo` varchar(10) NOT NULL,
  `banco` varchar(255) NOT NULL,
  `cuenta_bancaria` varchar(255) DEFAULT NULL,
  `hijos` int(11) NOT NULL DEFAULT 0,
  `instruccion_academica` int(11) NOT NULL DEFAULT 0,
  `discapacidades` int(2) NOT NULL DEFAULT 0,
  `tipo_cuenta` int(10) NOT NULL,
  `tipo_nomina` int(10) NOT NULL,
  `id_dependencia` int(255) NOT NULL,
  `verificado` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nacionalidad`, `cedula`, `cod_empleado`, `nombres`, `fecha_ingreso`, `otros_años`, `status`, `observacion`, `cod_cargo`, `banco`, `cuenta_bancaria`, `hijos`, `instruccion_academica`, `discapacidades`, `tipo_cuenta`, `tipo_nomina`, `id_dependencia`, `verificado`) VALUES
(27, 'V', '23987719', '441151', 'ORQUIDEA JOSE BOSSIO ALDANA', '2010-05-02', 0, '1', 'N/A', '25212', 'Tesoro', '01630409334091000513', 3, 3, 0, 0, 0, 6, 1),
(28, 'V', '4781808', '441151', 'BOSSIO CORREA, HERNAN ARSENIO', '2021-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '01020457790100627238', 3, 2, 0, 0, 0, 6, 1),
(29, 'V', '1566323', '441151', 'LARA, ALIDA DEL VALLE', '2021-05-02', 0, '1', 'N/A', '25212', 'Caroni', '01280027402712476304', 3, 3, 0, 1, 0, 6, 1),
(30, 'V', '642362', '441151', 'NANCY GISELA AGUILAR DE MENOTTI', '2021-05-02', 0, '1', 'N/A', '25212', 'Bicentenario', '01750575120077277895', 3, 2, 0, 0, 0, 6, 1),
(31, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 3, 0, 0, 0, 6, 0),
(32, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 2, 0, 0, 0, 6, 0),
(33, '1', '123456789', '441151', 'Pedro Pablo', '2022-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 3, 0, 0, 0, 6, 0),
(34, '1', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 2, 0, 1, 2, 6, 0),
(35, '1', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 3, 0, 1, 2, 6, 0),
(37, '1', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 2, 0, 1, 2, 6, 0),
(38, 'V', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 3, 0, 1, 2, 6, 0),
(39, 'V', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 2, 0, 1, 2, 6, 0),
(40, 'V', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 3, 0, 1, 2, 6, 0),
(41, 'V123456', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', 'Venezuela', '1002555541124', 3, 2, 0, 1, 2, 6, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `informacion_pdf`
--

CREATE TABLE `informacion_pdf` (
  `id` int(255) NOT NULL,
  `cedula` varchar(2000) NOT NULL,
  `total_pagar` varchar(2000) NOT NULL,
  `correlativo` varchar(255) NOT NULL,
  `identificador` varchar(255) NOT NULL,
  `banco` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `informacion_pdf`
--

INSERT INTO `informacion_pdf` (`id`, `cedula`, `total_pagar`, `correlativo`, `identificador`, `banco`) VALUES
(25, '[\"4781808\"]', '[\"82.03\"]', '00001', 's1', 'Venezuela'),
(26, '[\"23987719\"]', '[\"112.8\"]', '00001', 's1', 'Tesoro'),
(27, '[\"1566323\"]', '[\"82.03\"]', '00001', 's1', 'Caroni'),
(28, '[\"642362\"]', '[\"82.03\"]', '00001', 's1', 'Bicentenario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas`
--

CREATE TABLE `nominas` (
  `id` int(11) NOT NULL,
  `grupo_nomina` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `frecuencia` varchar(255) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `conceptos_aplicados` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `nominas`
--

INSERT INTO `nominas` (`id`, `grupo_nomina`, `nombre`, `frecuencia`, `tipo`, `conceptos_aplicados`) VALUES
(5, '3', 'Obreros', '1', '1', '[22,23,24,25,26,27,28,29,30,31,32,33,34,21]');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
-- Estructura de tabla para la tabla `nominas_grupos`
--

CREATE TABLE `nominas_grupos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `nominas_grupos`
--

INSERT INTO `nominas_grupos` (`id`, `codigo`, `nombre`, `creado`) VALUES
(2, '015', 'Empleados contratados', '2024-05-13 20:11:37'),
(3, '003', 'Obreros', '2024-05-13 20:18:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` varchar(255) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peticiones`
--

CREATE TABLE `peticiones` (
  `id` int(255) NOT NULL,
  `empleados` varchar(255) NOT NULL,
  `asignaciones` varchar(2000) NOT NULL,
  `deducciones` varchar(2000) NOT NULL,
  `aportes` varchar(2000) NOT NULL,
  `total_pagar` varchar(255) NOT NULL,
  `correlativo` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `nombre_nomina` varchar(255) NOT NULL,
  `creacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `peticiones`
--

INSERT INTO `peticiones` (`id`, `empleados`, `asignaciones`, `deducciones`, `aportes`, `total_pagar`, `correlativo`, `status`, `nombre_nomina`, `creacion`) VALUES
(15, '[27,28,29,30]', '{\"CONTRIBUCION POR DISCAPACIDAD\":40,\"PRIMA POR HIJO EMPLEADOS\":20,\"PRIMA POR TRANSPORTE\":200,\"PRIMA POR ANTIGUEDAD EMPLEADOS\":40,\"PRIMA POR ESCALAFON\":20,\"PRIMA POR FRONTERA\":20,\"PRIMA POR PROFESIONALES\":80,\"PAGO DE BECA\":40,\"PRIMA P\\/DED AL S\\/PUBLICO UNICO DE SALUD\":20}', '{\"S. S. O\":20,\"RPE\":20}', '{\"A\\/P S.S.O\":20,\"A\\/P RPE\":20}', '[451.18,328.12,328.12,328.12]', '00001', '1', 'Obreros', '2024-06-17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `primantiguedad`
--

CREATE TABLE `primantiguedad` (
  `id` int(255) NOT NULL,
  `porcentaje` varchar(255) NOT NULL,
  `tiempo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `primantiguedad`
--

INSERT INTO `primantiguedad` (`id`, `porcentaje`, `tiempo`) VALUES
(1, '1', '1'),
(2, '2', '2'),
(3, '3', '3'),
(4, '4', '4'),
(5, '5', '5'),
(6, '6.20', '6'),
(7, '7.40', '7'),
(8, '8.60', '8'),
(9, '9.80', '9'),
(10, '11', '10'),
(11, '12.40', '11'),
(12, '13.80', '12'),
(13, '15.20', '13'),
(14, '16.60', '14'),
(15, '18', '15'),
(16, '19.60', '16'),
(17, '21.20', '17'),
(18, '22.80', '18'),
(19, '24.40', '19'),
(20, '26', '20'),
(21, '27.80', '21'),
(22, '29.60', '22'),
(23, '30', '23'),
(24, '0', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesiones`
--

CREATE TABLE `profesiones` (
  `id_profesion` int(255) NOT NULL,
  `profesion` varchar(255) NOT NULL,
  `porcentaje` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesiones`
--

INSERT INTO `profesiones` (`id_profesion`, `profesion`, `porcentaje`) VALUES
(2, 'TECNICO SUPERIOR UNIVERSITARIO', '20'),
(3, 'PROFESIONAL', '25'),
(4, 'ESPECIALISTA', '30'),
(5, 'MAESTRIA', '35'),
(6, 'DOCTOR', '40'),
(7, 'Sin Profesionalizacion', '0');

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
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `u_nivel` int(11) NOT NULL,
  `u_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `system_users`
--

INSERT INTO `system_users` (`u_id`, `u_nombre`, `u_oficina_id`, `u_oficina`, `u_email`, `u_contrasena`, `creado`, `u_nivel`, `u_status`) VALUES
(31, 'user Nombre', 1, 'Nomina', 'corro@correo.com', '$2y$10$EyP1MOY39kuw4uREdk7ao.UUzQ10YNIZ95IZLM70MUPo5J6YzEBVG', '2024-03-07 11:18:19', 1, 1),
(33, 'otro user', 1, 'Nomina', 'ots@gmail.com', '$2y$10$uHhoK5UNls/rvTrmVCia.eTxE3b2eCp5IFHCsS1j0FOacCTj3bQ8C', '2024-05-29 16:32:32', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tabuladores`
--

CREATE TABLE `tabuladores` (
  `id` int(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `grados` varchar(255) NOT NULL,
  `pasos` varchar(255) NOT NULL,
  `aniosPasos` varchar(255) NOT NULL,
  `timestamp` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tabuladores`
--

INSERT INTO `tabuladores` (`id`, `nombre`, `grados`, `pasos`, `aniosPasos`, `timestamp`) VALUES
(32, 'tabulador regional 001', '26', '15', '1', '2024-05-22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tabuladores_estr`
--

CREATE TABLE `tabuladores_estr` (
  `id` int(255) NOT NULL,
  `paso` varchar(255) NOT NULL,
  `grado` varchar(255) NOT NULL,
  `monto` varchar(255) NOT NULL,
  `tabulador_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tabuladores_estr`
--

INSERT INTO `tabuladores_estr` (`id`, `paso`, `grado`, `monto`, `tabulador_id`) VALUES
(280, 'P1', 'G1', '195', 32),
(281, 'P2', 'G1', '202.8', 32),
(282, 'P3', 'G1', '210.91', 32),
(283, 'P4', 'G1', '219.35', 32),
(284, 'P5', 'G1', '228.12', 32),
(285, 'P6', 'G1', '237.25', 32),
(286, 'P7', 'G1', '246.74', 32),
(287, 'P8', 'G1', '256.61', 32),
(288, 'P9', 'G1', '266.87', 32),
(289, 'P10', 'G1', '277.65', 32),
(290, 'P11', 'G1', '288.65', 32),
(291, 'P12', 'G1', '300.19', 32),
(292, 'P13', 'G1', '312.2', 32),
(293, 'P14', 'G1', '324.69', 32),
(294, 'P15', 'G1', '337.68', 32),
(295, 'P1', 'G2', '202.8', 32),
(296, 'P2', 'G2', '210.91', 32),
(297, 'P3', 'G2', '219.35', 32),
(298, 'P4', 'G2', '228.12', 32),
(299, 'P5', 'G2', '237.25', 32),
(300, 'P6', 'G2', '246.74', 32),
(301, 'P7', 'G2', '256.61', 32),
(302, 'P8', 'G2', '266.87', 32),
(303, 'P9', 'G2', '277.65', 32),
(304, 'P10', 'G2', '288.65', 32),
(305, 'P11', 'G2', '300.19', 32),
(306, 'P12', 'G2', '312.2', 32),
(307, 'P13', 'G2', '324.69', 32),
(308, 'P14', 'G2', '337.68', 32),
(309, 'P15', 'G2', '351.18', 32),
(310, 'P1', 'G3', '210.91', 32),
(311, 'P2', 'G3', '219.35', 32),
(312, 'P3', 'G3', '228.12', 32),
(313, 'P4', 'G3', '237.25', 32),
(314, 'P5', 'G3', '246.74', 32),
(315, 'P6', 'G3', '256.61', 32),
(316, 'P7', 'G3', '266.87', 32),
(317, 'P8', 'G3', '277.55', 32),
(318, 'P9', 'G3', '288.65', 32),
(319, 'P10', 'G3', '300.19', 32),
(320, 'P11', 'G3', '312.2', 32),
(321, 'P12', 'G3', '324.69', 32),
(322, 'P13', 'G3', '337.68', 32),
(323, 'P14', 'G3', '351.18', 32),
(324, 'P15', 'G3', '365.23', 32),
(325, 'P1', 'G4', '219.35', 32),
(326, 'P2', 'G4', '228.12', 32),
(327, 'P3', 'G4', '237.25', 32),
(328, 'P4', 'G4', '246.74', 32),
(329, 'P5', 'G4', '256.61', 32),
(330, 'P6', 'G4', '266.87', 32),
(331, 'P7', 'G4', '277.55', 32),
(332, 'P8', 'G4', '288.65', 32),
(333, 'P9', 'G4', '300.19', 32),
(334, 'P10', 'G4', '312.2', 32),
(335, 'P11', 'G4', '324.69', 32),
(336, 'P12', 'G4', '337.68', 32),
(337, 'P13', 'G4', '351.18', 32),
(338, 'P14', 'G4', '365.23', 32),
(339, 'P15', 'G4', '379.84', 32),
(340, 'P1', 'G5', '228.12', 32),
(341, 'P2', 'G5', '237.25', 32),
(342, 'P3', 'G5', '246.74', 32),
(343, 'P4', 'G5', '256.61', 32),
(344, 'P5', 'G5', '266.87', 32),
(345, 'P6', 'G5', '277.55', 32),
(346, 'P7', 'G5', '288.65', 32),
(347, 'P8', 'G5', '300.19', 32),
(348, 'P9', 'G5', '312.2', 32),
(349, 'P10', 'G5', '324.69', 32),
(350, 'P11', 'G5', '337.68', 32),
(351, 'P12', 'G5', '351.18', 32),
(352, 'P13', 'G5', '365.23', 32),
(353, 'P14', 'G5', '379.84', 32),
(354, 'P15', 'G5', '395.03', 32),
(355, 'P1', 'G6', '237.25', 32),
(356, 'P2', 'G6', '246.74', 32),
(357, 'P3', 'G6', '256.61', 32),
(358, 'P4', 'G6', '266.87', 32),
(359, 'P5', 'G6', '277.55', 32),
(360, 'P6', 'G6', '288.65', 32),
(361, 'P7', 'G6', '300.19', 32),
(362, 'P8', 'G6', '312.2', 32),
(363, 'P9', 'G6', '324.69', 32),
(364, 'P10', 'G6', '377.68', 32),
(365, 'P11', 'G6', '351.18', 32),
(366, 'P12', 'G6', '365.23', 32),
(367, 'P13', 'G6', '379.84', 32),
(368, 'P14', 'G6', '395.03', 32),
(369, 'P15', 'G6', '410.84', 32),
(370, 'P1', 'G7', '246.74', 32),
(371, 'P2', 'G7', '256.61', 32),
(372, 'P3', 'G7', '266.87', 32),
(373, 'P4', 'G7', '277.55', 32),
(374, 'P5', 'G7', '288.65', 32),
(375, 'P6', 'G7', '300.19', 32),
(376, 'P7', 'G7', '312.2', 32),
(377, 'P8', 'G7', '324.69', 32),
(378, 'P9', 'G7', '377.68', 32),
(379, 'P10', 'G7', '351.18', 32),
(380, 'P11', 'G7', '365.23', 32),
(381, 'P12', 'G7', '379.84', 32),
(382, 'P13', 'G7', '395.03', 32),
(383, 'P14', 'G7', '410.84', 32),
(384, 'P15', 'G7', '427.27', 32),
(385, 'P1', 'G8', '256.61', 32),
(386, 'P2', 'G8', '266.87', 32),
(387, 'P3', 'G8', '277.55', 32),
(388, 'P4', 'G8', '288.65', 32),
(389, 'P5', 'G8', '300.19', 32),
(390, 'P6', 'G8', '312.2', 32),
(391, 'P7', 'G8', '324.69', 32),
(392, 'P8', 'G8', '377.68', 32),
(393, 'P9', 'G8', '351.18', 32),
(394, 'P10', 'G8', '365.23', 32),
(395, 'P11', 'G8', '379.84', 32),
(396, 'P12', 'G8', '395.03', 32),
(397, 'P13', 'G8', '410.84', 32),
(398, 'P14', 'G8', '427.27', 32),
(399, 'P15', 'G8', '444.36', 32),
(400, 'P1', 'G9', '266.87', 32),
(401, 'P2', 'G9', '277.55', 32),
(402, 'P3', 'G9', '288.65', 32),
(403, 'P4', 'G9', '300.19', 32),
(404, 'P5', 'G9', '312.2', 32),
(405, 'P6', 'G9', '324.69', 32),
(406, 'P7', 'G9', '377.68', 32),
(407, 'P8', 'G9', '351.18', 32),
(408, 'P9', 'G9', '365.23', 32),
(409, 'P10', 'G9', '379.84', 32),
(410, 'P11', 'G9', '395.03', 32),
(411, 'P12', 'G9', '410.84', 32),
(412, 'P13', 'G9', '427.27', 32),
(413, 'P14', 'G9', '444.36', 32),
(414, 'P15', 'G9', '462.13', 32),
(415, 'P1', 'G10', '277.55', 32),
(416, 'P2', 'G10', '288.65', 32),
(417, 'P3', 'G10', '300.19', 32),
(418, 'P4', 'G10', '312.2', 32),
(419, 'P5', 'G10', '324.69', 32),
(420, 'P6', 'G10', '377.68', 32),
(421, 'P7', 'G10', '351.18', 32),
(422, 'P8', 'G10', '365.23', 32),
(423, 'P9', 'G10', '379.84', 32),
(424, 'P10', 'G10', '395.03', 32),
(425, 'P11', 'G10', '410.84', 32),
(426, 'P12', 'G10', '427.27', 32),
(427, 'P13', 'G10', '444.36', 32),
(428, 'P14', 'G10', '462.13', 32),
(429, 'P15', 'G10', '480.62', 32),
(430, 'P1', 'G11', '288.65', 32),
(431, 'P2', 'G11', '300.19', 32),
(432, 'P3', 'G11', '312.2', 32),
(433, 'P4', 'G11', '324.69', 32),
(434, 'P5', 'G11', '377.68', 32),
(435, 'P6', 'G11', '351.18', 32),
(436, 'P7', 'G11', '365.23', 32),
(437, 'P8', 'G11', '379.84', 32),
(438, 'P9', 'G11', '395.03', 32),
(439, 'P10', 'G11', '410.84', 32),
(440, 'P11', 'G11', '427.27', 32),
(441, 'P12', 'G11', '444.36', 32),
(442, 'P13', 'G11', '462.13', 32),
(443, 'P14', 'G11', '480.62', 32),
(444, 'P15', 'G11', '499.84', 32),
(445, 'P1', 'G12', '300.19', 32),
(446, 'P2', 'G12', '312.2', 32),
(447, 'P3', 'G12', '324.69', 32),
(448, 'P4', 'G12', '377.68', 32),
(449, 'P5', 'G12', '351.18', 32),
(450, 'P6', 'G12', '365.23', 32),
(451, 'P7', 'G12', '379.84', 32),
(452, 'P8', 'G12', '395.03', 32),
(453, 'P9', 'G12', '410.84', 32),
(454, 'P10', 'G12', '427.27', 32),
(455, 'P11', 'G12', '444.36', 32),
(456, 'P12', 'G12', '462.13', 32),
(457, 'P13', 'G12', '480.62', 32),
(458, 'P14', 'G12', '499.84', 32),
(459, 'P15', 'G12', '519.84', 32),
(460, 'P1', 'G13', '312.2', 32),
(461, 'P2', 'G13', '324.69', 32),
(462, 'P3', 'G13', '377.68', 32),
(463, 'P4', 'G13', '351.18', 32),
(464, 'P5', 'G13', '365.23', 32),
(465, 'P6', 'G13', '379.84', 32),
(466, 'P7', 'G13', '395.03', 32),
(467, 'P8', 'G13', '410.84', 32),
(468, 'P9', 'G13', '427.27', 32),
(469, 'P10', 'G13', '444.36', 32),
(470, 'P11', 'G13', '462.13', 32),
(471, 'P12', 'G13', '480.62', 32),
(472, 'P13', 'G13', '499.84', 32),
(473, 'P14', 'G13', '519.84', 32),
(474, 'P15', 'G13', '540.63', 32),
(475, 'P1', 'G14', '324.69', 32),
(476, 'P2', 'G14', '377.68', 32),
(477, 'P3', 'G14', '351.18', 32),
(478, 'P4', 'G14', '365.23', 32),
(479, 'P5', 'G14', '379.84', 32),
(480, 'P6', 'G14', '395.03', 32),
(481, 'P7', 'G14', '410.84', 32),
(482, 'P8', 'G14', '427.27', 32),
(483, 'P9', 'G14', '444.36', 32),
(484, 'P10', 'G14', '462.13', 32),
(485, 'P11', 'G14', '480.62', 32),
(486, 'P12', 'G14', '499.84', 32),
(487, 'P13', 'G14', '519.84', 32),
(488, 'P14', 'G14', '540.63', 32),
(489, 'P15', 'G14', '562.26', 32),
(490, 'P1', 'G15', '377.68', 32),
(491, 'P2', 'G15', '351.18', 32),
(492, 'P3', 'G15', '365.23', 32),
(493, 'P4', 'G15', '379.84', 32),
(494, 'P5', 'G15', '395.03', 32),
(495, 'P6', 'G15', '410.84', 32),
(496, 'P7', 'G15', '427.27', 32),
(497, 'P8', 'G15', '444.36', 32),
(498, 'P9', 'G15', '462.13', 32),
(499, 'P10', 'G15', '480.62', 32),
(500, 'P11', 'G15', '499.84', 32),
(501, 'P12', 'G15', '519.84', 32),
(502, 'P13', 'G15', '540.63', 32),
(503, 'P14', 'G15', '562.26', 32),
(504, 'P15', 'G15', '584.75', 32),
(505, 'P1', 'G16', '357.18', 32),
(506, 'P2', 'G16', '365.23', 32),
(507, 'P3', 'G16', '379.84', 32),
(508, 'P4', 'G16', '395.03', 32),
(509, 'P5', 'G16', '410.84', 32),
(510, 'P6', 'G16', '427.27', 32),
(511, 'P7', 'G16', '444.36', 32),
(512, 'P8', 'G16', '462.13', 32),
(513, 'P9', 'G16', '480.62', 32),
(514, 'P10', 'G16', '499.84', 32),
(515, 'P11', 'G16', '519.84', 32),
(516, 'P12', 'G16', '540.63', 32),
(517, 'P13', 'G16', '562.26', 32),
(518, 'P14', 'G16', '584.75', 32),
(519, 'P15', 'G16', '608.14', 32),
(520, 'P1', 'G17', '365.23', 32),
(521, 'P2', 'G17', '379.84', 32),
(522, 'P3', 'G17', '395.03', 32),
(523, 'P4', 'G17', '410.84', 32),
(524, 'P5', 'G17', '427.27', 32),
(525, 'P6', 'G17', '444.36', 32),
(526, 'P7', 'G17', '462.13', 32),
(527, 'P8', 'G17', '480.62', 32),
(528, 'P9', 'G17', '499.84', 32),
(529, 'P10', 'G17', '519.84', 32),
(530, 'P11', 'G17', '540.63', 32),
(531, 'P12', 'G17', '562.26', 32),
(532, 'P13', 'G17', '584.75', 32),
(533, 'P14', 'G17', '608.14', 32),
(534, 'P15', 'G17', '632.46', 32),
(535, 'P1', 'G18', '379.84', 32),
(536, 'P2', 'G18', '395.03', 32),
(537, 'P3', 'G18', '410.84', 32),
(538, 'P4', 'G18', '427.27', 32),
(539, 'P5', 'G18', '444.36', 32),
(540, 'P6', 'G18', '462.13', 32),
(541, 'P7', 'G18', '480.62', 32),
(542, 'P8', 'G18', '499.84', 32),
(543, 'P9', 'G18', '519.84', 32),
(544, 'P10', 'G18', '540.63', 32),
(545, 'P11', 'G18', '562.26', 32),
(546, 'P12', 'G18', '584.75', 32),
(547, 'P13', 'G18', '608.14', 32),
(548, 'P14', 'G18', '632.46', 32),
(549, 'P15', 'G18', '657.76', 32),
(550, 'P1', 'G19', '395.03', 32),
(551, 'P2', 'G19', '410.84', 32),
(552, 'P3', 'G19', '427.27', 32),
(553, 'P4', 'G19', '444.36', 32),
(554, 'P5', 'G19', '462.13', 32),
(555, 'P6', 'G19', '480.62', 32),
(556, 'P7', 'G19', '499.84', 32),
(557, 'P8', 'G19', '519.84', 32),
(558, 'P9', 'G19', '540.63', 32),
(559, 'P10', 'G19', '562.26', 32),
(560, 'P11', 'G19', '584.75', 32),
(561, 'P12', 'G19', '608.14', 32),
(562, 'P13', 'G19', '632.46', 32),
(563, 'P14', 'G19', '657.76', 32),
(564, 'P15', 'G19', '684.07', 32),
(565, 'P1', 'G20', '410.84', 32),
(566, 'P2', 'G20', '427.27', 32),
(567, 'P3', 'G20', '444.36', 32),
(568, 'P4', 'G20', '462.13', 32),
(569, 'P5', 'G20', '480.62', 32),
(570, 'P6', 'G20', '499.84', 32),
(571, 'P7', 'G20', '519.84', 32),
(572, 'P8', 'G20', '540.63', 32),
(573, 'P9', 'G20', '562.26', 32),
(574, 'P10', 'G20', '584.75', 32),
(575, 'P11', 'G20', '608.14', 32),
(576, 'P12', 'G20', '632.46', 32),
(577, 'P13', 'G20', '657.76', 32),
(578, 'P14', 'G20', '684.07', 32),
(579, 'P15', 'G20', '711.43', 32),
(580, 'P1', 'G21', '427.27', 32),
(581, 'P2', 'G21', '444.36', 32),
(582, 'P3', 'G21', '462.13', 32),
(583, 'P4', 'G21', '480.62', 32),
(584, 'P5', 'G21', '499.84', 32),
(585, 'P6', 'G21', '519.84', 32),
(586, 'P7', 'G21', '540.63', 32),
(587, 'P8', 'G21', '562.26', 32),
(588, 'P9', 'G21', '584.75', 32),
(589, 'P10', 'G21', '608.14', 32),
(590, 'P11', 'G21', '632.46', 32),
(591, 'P12', 'G21', '657.76', 32),
(592, 'P13', 'G21', '684.07', 32),
(593, 'P14', 'G21', '711.43', 32),
(594, 'P15', 'G21', '739.89', 32),
(595, 'P1', 'G22', '444.36', 32),
(596, 'P2', 'G22', '462.13', 32),
(597, 'P3', 'G22', '480.62', 32),
(598, 'P4', 'G22', '499.84', 32),
(599, 'P5', 'G22', '519.84', 32),
(600, 'P6', 'G22', '540.63', 32),
(601, 'P7', 'G22', '562.26', 32),
(602, 'P8', 'G22', '584.75', 32),
(603, 'P9', 'G22', '608.14', 32),
(604, 'P10', 'G22', '632.46', 32),
(605, 'P11', 'G22', '657.76', 32),
(606, 'P12', 'G22', '684.07', 32),
(607, 'P13', 'G22', '711.43', 32),
(608, 'P14', 'G22', '739.89', 32),
(609, 'P15', 'G22', '769.79', 32),
(610, 'P1', 'G23', '462.13', 32),
(611, 'P2', 'G23', '480.62', 32),
(612, 'P3', 'G23', '499.84', 32),
(613, 'P4', 'G23', '519.84', 32),
(614, 'P5', 'G23', '540.63', 32),
(615, 'P6', 'G23', '562.26', 32),
(616, 'P7', 'G23', '584.75', 32),
(617, 'P8', 'G23', '608.14', 32),
(618, 'P9', 'G23', '632.46', 32),
(619, 'P10', 'G23', '657.76', 32),
(620, 'P11', 'G23', '684.07', 32),
(621, 'P12', 'G23', '711.43', 32),
(622, 'P13', 'G23', '739.89', 32),
(623, 'P14', 'G23', '769.79', 32),
(624, 'P15', 'G23', '800.27', 32),
(625, 'P1', 'G24', '480.62', 32),
(626, 'P2', 'G24', '499.84', 32),
(627, 'P3', 'G24', '519.84', 32),
(628, 'P4', 'G24', '540.63', 32),
(629, 'P5', 'G24', '562.26', 32),
(630, 'P6', 'G24', '584.75', 32),
(631, 'P7', 'G24', '608.14', 32),
(632, 'P8', 'G24', '632.46', 32),
(633, 'P9', 'G24', '657.76', 32),
(634, 'P10', 'G24', '684.07', 32),
(635, 'P11', 'G24', '711.43', 32),
(636, 'P12', 'G24', '739.89', 32),
(637, 'P13', 'G24', '769.79', 32),
(638, 'P14', 'G24', '800.27', 32),
(639, 'P15', 'G24', '832.28', 32),
(640, 'P1', 'G25', '499.84', 32),
(641, 'P2', 'G25', '519.84', 32),
(642, 'P3', 'G25', '540.63', 32),
(643, 'P4', 'G25', '562.26', 32),
(644, 'P5', 'G25', '584.75', 32),
(645, 'P6', 'G25', '608.14', 32),
(646, 'P7', 'G25', '632.46', 32),
(647, 'P8', 'G25', '657.76', 32),
(648, 'P9', 'G25', '684.07', 32),
(649, 'P10', 'G25', '711.43', 32),
(650, 'P11', 'G25', '739.89', 32),
(651, 'P12', 'G25', '769.79', 32),
(652, 'P13', 'G25', '800.27', 32),
(653, 'P14', 'G25', '832.28', 32),
(654, 'P15', 'G25', '865.57', 32),
(655, 'P1', 'G26', '519.84', 32),
(656, 'P2', 'G26', '540.63', 32),
(657, 'P3', 'G26', '562.26', 32),
(658, 'P4', 'G26', '584.75', 32),
(659, 'P5', 'G26', '608.14', 32),
(660, 'P6', 'G26', '632.46', 32),
(661, 'P7', 'G26', '657.76', 32),
(662, 'P8', 'G26', '684.07', 32),
(663, 'P9', 'G26', '711.43', 32),
(664, 'P10', 'G26', '739.89', 32),
(665, 'P11', 'G26', '769.79', 32),
(666, 'P12', 'G26', '800.27', 32),
(667, 'P13', 'G26', '832.28', 32),
(668, 'P14', 'G26', '865.57', 32),
(669, 'P15', 'G26', '900.19', 32);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `txt`
--

CREATE TABLE `txt` (
  `id` int(255) NOT NULL,
  `id_empleado` int(255) NOT NULL,
  `total_a_pagar` varchar(255) NOT NULL,
  `nombre_nomina` varchar(255) NOT NULL,
  `identificador` varchar(255) NOT NULL,
  `fecha_pagar` varchar(255) NOT NULL,
  `correlativo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `txt`
--

INSERT INTO `txt` (`id`, `id_empleado`, `total_a_pagar`, `nombre_nomina`, `identificador`, `fecha_pagar`, `correlativo`) VALUES
(807, 27, '112.8', 'Obreros', 's1', '06-2024', '00001'),
(808, 27, '112.8', 'Obreros', 's2', '06-2024', '00001'),
(809, 27, '112.8', 'Obreros', 's3', '06-2024', '00001'),
(810, 27, '112.8', 'Obreros', 's4', '06-2024', '00001'),
(811, 28, '82.03', 'Obreros', 's1', '06-2024', '00001'),
(812, 28, '82.03', 'Obreros', 's2', '06-2024', '00001'),
(813, 28, '82.03', 'Obreros', 's3', '06-2024', '00001'),
(814, 28, '82.03', 'Obreros', 's4', '06-2024', '00001'),
(815, 29, '82.03', 'Obreros', 's1', '06-2024', '00001'),
(816, 29, '82.03', 'Obreros', 's2', '06-2024', '00001'),
(817, 29, '82.03', 'Obreros', 's3', '06-2024', '00001'),
(818, 29, '82.03', 'Obreros', 's4', '06-2024', '00001'),
(819, 30, '82.03', 'Obreros', 's1', '06-2024', '00001'),
(820, 30, '82.03', 'Obreros', 's2', '06-2024', '00001'),
(821, 30, '82.03', 'Obreros', 's3', '06-2024', '00001'),
(822, 30, '82.03', 'Obreros', 's4', '06-2024', '00001');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cargos_grados`
--
ALTER TABLE `cargos_grados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `conceptos`
--
ALTER TABLE `conceptos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `conceptos_aplicados`
--
ALTER TABLE `conceptos_aplicados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `conceptos_formulacion`
--
ALTER TABLE `conceptos_formulacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `dependencias`
--
ALTER TABLE `dependencias`
  ADD PRIMARY KEY (`id_dependencia`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `informacion_pdf`
--
ALTER TABLE `informacion_pdf`
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
-- Indices de la tabla `nominas_grupos`
--
ALTER TABLE `nominas_grupos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `peticiones`
--
ALTER TABLE `peticiones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `primantiguedad`
--
ALTER TABLE `primantiguedad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `profesiones`
--
ALTER TABLE `profesiones`
  ADD PRIMARY KEY (`id_profesion`);

--
-- Indices de la tabla `system_users`
--
ALTER TABLE `system_users`
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `usuario` (`u_email`);

--
-- Indices de la tabla `tabuladores`
--
ALTER TABLE `tabuladores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tabuladores_estr`
--
ALTER TABLE `tabuladores_estr`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `txt`
--
ALTER TABLE `txt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cargos_grados`
--
ALTER TABLE `cargos_grados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=465;

--
-- AUTO_INCREMENT de la tabla `conceptos`
--
ALTER TABLE `conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `conceptos_aplicados`
--
ALTER TABLE `conceptos_aplicados`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `conceptos_formulacion`
--
ALTER TABLE `conceptos_formulacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `dependencias`
--
ALTER TABLE `dependencias`
  MODIFY `id_dependencia` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `informacion_pdf`
--
ALTER TABLE `informacion_pdf`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `nominas`
--
ALTER TABLE `nominas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `nominas_conceptos`
--
ALTER TABLE `nominas_conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `nominas_grupos`
--
ALTER TABLE `nominas_grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `peticiones`
--
ALTER TABLE `peticiones`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `primantiguedad`
--
ALTER TABLE `primantiguedad`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `profesiones`
--
ALTER TABLE `profesiones`
  MODIFY `id_profesion` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `system_users`
--
ALTER TABLE `system_users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `tabuladores`
--
ALTER TABLE `tabuladores`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `tabuladores_estr`
--
ALTER TABLE `tabuladores_estr`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=670;

--
-- AUTO_INCREMENT de la tabla `txt`
--
ALTER TABLE `txt`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=823;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
