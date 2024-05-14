-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-05-2024 a las 19:08:25
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
  `tipo_concepto` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `conceptos`
--

INSERT INTO `conceptos` (`id`, `nom_concepto`, `cod_partida`, `tipo_concepto`) VALUES
(1, 'SUELDO', '401-01-01-00-0000', 'A'),
(2, 'PRIMA POR HIJO EMPLEADOS', '401-03-04-00-0000', 'A'),
(3, 'PRIMA POR TRANSPORTE', '401-03-02-00-0000', 'A'),
(4, 'PRIMA POR ANTIGUEDAD EMPLEADOS', '401-03-49-00-0000', 'A'),
(5, 'PRIMA POR ESCALAFON', '401-03-98-00-0001', 'A'),
(6, 'PRIMA POR FRONTERA', '401-03-97-00-0001', 'A'),
(7, 'PRIMA POR ANTIGUEDAD (ESPECIAL)', '401-03-09-00-0000', 'A'),
(8, 'PRIMA P/DED AL S/PUBLICO UNICO DE SALUD', '401-03-98-00-0005', 'A'),
(9, 'PRIMA POR PROFESIONALES', '401-03-08-00-0000', 'A'),
(10, 'CONTRIBUCION POR DISCAPACIDAD', '401-03-98-00-0006', 'A'),
(11, 'PAGO DE BECA', '401-07-18-00-0000', 'A'),
(12, 'S. S. O', '401-01-01-00-0000', 'D'),
(13, 'RPE', '401-01-02-00-0000', 'D'),
(14, 'A/P S.S.O', '401-06-01-00-0000', 'P'),
(15, 'A/P RPE', '401-06-12-00-0000', 'P');

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
  `nacionalidad` varchar(1) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `cod_empleado` varchar(20) DEFAULT NULL,
  `nombres` varchar(255) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `otros_años` int(11) NOT NULL DEFAULT 0,
  `status` varchar(5) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `cod_cargo` varchar(10) NOT NULL,
  `cargo` varchar(255) NOT NULL,
  `banco` varchar(255) NOT NULL,
  `cuenta_bancaria` varchar(25) DEFAULT NULL,
  `hijos` int(11) NOT NULL DEFAULT 0,
  `instruccion_academica` int(11) NOT NULL DEFAULT 0,
  `discapacidades` int(2) NOT NULL DEFAULT 0,
  `becas` int(2) NOT NULL,
  `tipo_cuenta` int(10) NOT NULL,
  `tipo_nomina` int(10) NOT NULL,
  `id_dependencia` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nacionalidad`, `cedula`, `cod_empleado`, `nombres`, `fecha_ingreso`, `otros_años`, `status`, `observacion`, `cod_cargo`, `cargo`, `banco`, `cuenta_bancaria`, `hijos`, `instruccion_academica`, `discapacidades`, `becas`, `tipo_cuenta`, `tipo_nomina`, `id_dependencia`) VALUES
(26, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(27, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(28, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(29, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(30, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(31, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(32, '1', '123456789', '441151', 'Pedro Pablo', '2021-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(33, '1', '123456789', '441151', 'Pedro Pablo', '2022-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 0, 0, 0),
(34, '1', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 1, 2, 6),
(35, '1', '123456789', '441151', 'Pedro Pablo', '2010-05-02', 0, '1', 'N/A', '25212', '12', 'Venezuela', '1002555541124', 3, 1, 0, 0, 1, 2, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas`
--

CREATE TABLE `nominas` (
  `id` int(11) NOT NULL,
  `codigo` int(20) NOT NULL,
  `nombre` varchar(255) NOT NULL
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
  `creado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `system_users`
--

INSERT INTO `system_users` (`u_id`, `u_nombre`, `u_oficina_id`, `u_oficina`, `u_email`, `u_contrasena`, `creado`) VALUES
(31, 'user Nombre', 1, 'Nomina', 'corro@correo.com', '$2y$10$EyP1MOY39kuw4uREdk7ao.UUzQ10YNIZ95IZLM70MUPo5J6YzEBVG', '2024-03-07 11:18:19');

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
(29, 'tabulador_regional_001', '3', '3', '1', '2024-05-13');

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
(253, 'P1', 'G1', '1', 29),
(254, 'P2', 'G1', '1', 29),
(255, 'P3', 'G1', '1', 29),
(256, 'P1', 'G2', '1', 29),
(257, 'P2', 'G2', '1', 29),
(258, 'P3', 'G2', '1', 29),
(259, 'P1', 'G3', '1', 29),
(260, 'P2', 'G3', '1', 29),
(261, 'P3', 'G3', '249.31', 29);

--
-- Índices para tablas volcadas
--

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargos_grados`
--
ALTER TABLE `cargos_grados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=465;

--
-- AUTO_INCREMENT de la tabla `conceptos`
--
ALTER TABLE `conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `dependencias`
--
ALTER TABLE `dependencias`
  MODIFY `id_dependencia` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

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
-- AUTO_INCREMENT de la tabla `nominas_grupos`
--
ALTER TABLE `nominas_grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `profesiones`
--
ALTER TABLE `profesiones`
  MODIFY `id_profesion` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `system_users`
--
ALTER TABLE `system_users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `tabuladores`
--
ALTER TABLE `tabuladores`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `tabuladores_estr`
--
ALTER TABLE `tabuladores_estr`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
