-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 23-03-2026 a las 22:08:26
-- Versión del servidor: 8.4.3
-- Versión de PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `soluciones_gliese`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employees`
--

CREATE TABLE `employees` (
  `id` bigint NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `document_type_id` int DEFAULT NULL,
  `document_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_person` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `brand` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `license_plate` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `driver_license` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `manager` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `role_person_id` int DEFAULT NULL,
  `type_person` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `work_area` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `employees`
--

INSERT INTO `employees` (`id`, `name`, `document_type_id`, `document_number`, `address`, `phone`, `email`, `contact_person`, `brand`, `license_plate`, `driver_license`, `manager`, `reference`, `status`, `role_person_id`, `type_person`, `work_area`, `position`, `salary`) VALUES
(1, 'VICTOR ENRIQUE VALDEZ PACHECO', 1, '72757455', 'Bellavista 266', '940168728', 'valdezv231@gmail.com', NULL, NULL, NULL, NULL, NULL, '11111', 1, 1, NULL, 'Sistemas', 'Desarrollador', 4500.00),
(2, 'LEONELLA KEYLA PINTO JARA', 1, '76841363', 'mz d2 lt8', '972040780', 'leonellapj9@gmail.com', NULL, NULL, NULL, NULL, NULL, '', 1, 1, NULL, 'Administración', 'Asistente', 2800.00),
(3, 'CORPORACION DE SERVICIOS ESPECIALES JQ S.A.C.', 2, '20531050259', 'AV. SALVADOR DEL SOLAR MZA. G LOTE 04 URB. EL MILAGRO - II ETAPA', '996720630', 'wilderjulca@solucionesintegralesjb.com', NULL, NULL, NULL, NULL, NULL, '', 1, 1, NULL, 'Operaciones', 'Proveedor', 0.00),
(7, 'MARIA GONZALES', 1, '12345678', 'av. la paz 123', '987654321', 'maria@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 'Ventas', 'Ejecutivo', 3000.00),
(8, 'EMPRESA XYZ S.A.C.', 2, '20456789333', 'calle ficticia 456', '123456789', 'contacto@xyz.com', NULL, 'XYZ Brand', NULL, NULL, NULL, NULL, 1, 2, NULL, 'Logística', 'Servicios Externos', 0.00),
(9, 'JUAN PEREZ', 1, '87654321', 'av. siempre viva 789', '912345678', 'juan.perez@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 'Almacén', 'Auxiliar', 1500.00),
(10, 'COMERCIAL ABC E.I.R.L.', 2, '20567890333', 'jr. ejemplo 321', '321654987', 'info@comercialabc.com', NULL, 'ABC Brand', NULL, NULL, NULL, NULL, 1, 2, NULL, 'Compras', 'Consultoría', 0.00),
(11, 'ANA TORRES', 1, '23456789', 'av. los héroes 654', '654321987', 'ana.torres@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 'Recursos Humanos', 'Analista', 3200.00),
(16, 'ANTHONI OTINIANO', 1, '75832762', 'Lima', '921812289', 'otinianoantoni02@gmail.com', NULL, NULL, NULL, NULL, NULL, 'xd', 1, 1, NULL, 'Sistemas', 'Soporte Técnico', 2500.00),
(17, 'Jose Olazabal', 1, '72326596', 'Huacho', '992923544', 'olazabalsanchez5@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 'Gerencia', 'Jefe de área', 5000.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `document_number` (`document_number`),
  ADD KEY `role_person_id` (`role_person_id`),
  ADD KEY `document_type_id` (`document_type_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;