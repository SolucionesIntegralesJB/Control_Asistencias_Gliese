-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 23-03-2026 a las 21:37:02
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

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`soluciones`@`localhost` PROCEDURE `ActualizarContraseña` (IN `p_username` VARCHAR(50), IN `p_old_password` VARCHAR(255), IN `p_new_password` VARCHAR(255), OUT `p_success` BOOLEAN)   BEGIN
    DECLARE v_count INT;

    SELECT COUNT(*) INTO v_count
    FROM cuenta
    WHERE username = p_username
    AND password = SHA2(p_old_password, 256);

    IF v_count > 0 THEN
        UPDATE cuenta
        SET password = SHA2(p_new_password, 256)
        WHERE username = p_username;
        SET p_success = TRUE;
    ELSE
        SET p_success = FALSE;
    END IF;
END$$

CREATE DEFINER=`soluciones`@`localhost` PROCEDURE `IniciarSesion` (IN `p_username` VARCHAR(50), IN `p_password` VARCHAR(255), OUT `p_name` VARCHAR(150), OUT `p_document_number` VARCHAR(20))   BEGIN
    SELECT c.*, p.name, p.document_number
    INTO p_name, p_document_number
    FROM cuenta c
    INNER JOIN person p ON c.id_person = p.id
    WHERE c.username = p_username
    AND c.password = SHA2(p_password, 256)
    AND c.status = 1;

    -- Actualizar último login si las credenciales son correctas
    IF ROW_COUNT() > 0 THEN
        UPDATE cuenta
        SET last_login = NOW()
        WHERE username = p_username;
    END IF;
END$$

CREATE DEFINER=`soluciones`@`localhost` PROCEDURE `RegistrarUsuario` (IN `p_name` VARCHAR(150), IN `p_document_type_id` INT, IN `p_document_number` VARCHAR(20), IN `p_address` VARCHAR(150), IN `p_phone` VARCHAR(15), IN `p_email` VARCHAR(50), IN `p_username` VARCHAR(50), IN `p_password` VARCHAR(255), OUT `p_success` BOOLEAN)   BEGIN
    DECLARE v_person_id INT;

    -- Insertar en la tabla person
    INSERT INTO person (name, document_type_id, document_number, address, phone, email)
    VALUES (p_name, p_document_type_id, p_document_number, p_address, p_phone, p_email);

    -- Obtener el id de la persona recién insertada
    SET v_person_id = LAST_INSERT_ID();

    -- Insertar en la tabla cuenta
    INSERT INTO cuenta (id_person, username, password, email)
    VALUES (v_person_id, p_username, SHA2(p_password, 256), p_email);

    -- Indicar éxito
    SET p_success = TRUE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `billingpersale`
--

CREATE TABLE `billingpersale` (
  `id` bigint NOT NULL,
  `operation_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `campus_id` bigint DEFAULT NULL,
  `person_id` bigint DEFAULT NULL,
  `user_id` bigint DEFAULT NULL,
  `voucher_type` int DEFAULT NULL,
  `series` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correlative` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `issue_time` time DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` int DEFAULT NULL,
  `installments` int DEFAULT NULL,
  `installment_amount` decimal(10,2) DEFAULT NULL,
  `payment_medium` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taxable_operations` decimal(10,2) DEFAULT NULL,
  `free_operations` decimal(10,2) DEFAULT NULL,
  `exempt_operations` decimal(10,2) DEFAULT NULL,
  `unaffected_operations` decimal(10,2) DEFAULT NULL,
  `igv` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `leyend` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `retention` decimal(10,2) DEFAULT NULL,
  `retention_percentage` decimal(5,2) DEFAULT NULL,
  `retention_amount` decimal(10,2) DEFAULT NULL,
  `detraction` decimal(10,2) DEFAULT NULL,
  `detraction_percentage` decimal(5,2) DEFAULT NULL,
  `detraction_amount` decimal(10,2) DEFAULT NULL,
  `net_amount_pending_payment` decimal(10,2) DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `response` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `billingpersale`
--

INSERT INTO `billingpersale` (`id`, `operation_type`, `campus_id`, `person_id`, `user_id`, `voucher_type`, `series`, `correlative`, `issue_date`, `issue_time`, `due_date`, `currency`, `payment_method`, `installments`, `installment_amount`, `payment_medium`, `taxable_operations`, `free_operations`, `exempt_operations`, `unaffected_operations`, `igv`, `total_amount`, `leyend`, `retention`, `retention_percentage`, `retention_amount`, `detraction`, `detraction_percentage`, `detraction_amount`, `net_amount_pending_payment`, `status`, `response`) VALUES
(1, '0101', NULL, 1, 2, 2, 'B001', '00000001', '2024-10-21', '10:07:24', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 305.08, 0.00, 0.00, 0.00, 54.92, 360.00, 'TRESCIENTOS SESENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '0101', NULL, 1, 2, 2, 'B001', '00000002', '2024-10-21', '10:16:03', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 101.69, 101.69, 101.69, 101.69, 18.31, 323.38, 'TRESCIENTOS VEINTITRES Y 38/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(3, '0101', 4, 1, 2, 2, 'B001', '00000003', '2024-10-21', '10:19:28', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000003, ha sido aceptada'),
(4, '0101', 5, 1, 2, 2, 'B001', '00000004', '2024-10-21', '11:07:58', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(5, '0101', 4, 1, 2, 2, 'B001', '00000005', '2024-11-08', '12:57:29', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000005, ha sido aceptada'),
(6, '0101', 4, 1, 2, 2, 'B001', '00000006', '2024-11-08', '13:47:57', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000006, ha sido aceptada'),
(7, '0101', 4, 1, 2, 2, 'B001', '00000007', '2024-11-08', '14:00:41', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000007, ha sido aceptada'),
(8, '0101', 4, 1, 2, 2, 'B001', '00000008', '2024-11-08', '15:18:22', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000008, ha sido aceptada'),
(9, '0101', 4, 1, 2, 2, 'B001', '00000009', '2024-11-18', '10:31:03', '2024-11-18', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000009, ha sido aceptada'),
(10, '0101', 4, 1, 2, 2, 'B001', '00000010', '2024-11-19', '10:36:47', '2024-11-19', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(11, '0101', 4, 1, 2, 2, 'B001', '00000011', '2024-11-19', '23:39:21', '2024-11-19', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000011, ha sido aceptada'),
(12, '0101', 4, 1, 2, 2, 'B001', '00000012', '2023-11-30', '11:06:00', '2024-12-21', 'PEN', 1, NULL, NULL, '1', 47.46, 0.00, 0.00, 0.00, 8.54, 56.00, 'CINCUENTA Y SEIS Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(13, '0101', 4, 1, 2, 2, 'B001', '00000013', '2023-12-20', '14:49:14', '2024-11-21', 'PEN', 1, NULL, NULL, '1', 50.85, 0.00, 0.00, 0.00, 9.15, 60.00, 'SESENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(14, '0101', 4, 1, 2, 2, 'B001', '00000014', '2024-11-23', '20:03:15', '2024-11-23', 'PEN', 1, NULL, NULL, '1', 186.44, 0.00, 0.00, 0.00, 33.56, 220.00, 'DOSCIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(15, '0101', 4, 1, 2, 2, 'B001', '00000015', '2024-11-23', '20:04:09', '2024-11-23', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(16, '0101', 4, 1, 2, 2, 'B001', '00000016', '2024-12-25', '20:43:51', '2024-12-26', 'PEN', 1, NULL, NULL, '1', 42.37, 0.00, 0.00, 0.00, 7.63, 50.00, 'CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000016, ha sido aceptada'),
(17, '0101', 4, 1, 2, 2, 'B001', '00000017', '2024-11-30', '15:10:50', '2024-12-01', 'PEN', 1, NULL, NULL, '1', 33.90, 0.00, 0.00, 0.00, 6.10, 40.00, 'CUARENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000017, ha sido aceptada'),
(18, '0101', 4, 1, 2, 2, 'B001', '00000018', '2024-12-06', '16:38:18', '2024-12-16', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(19, '0101', 4, 1, 2, 2, 'B001', '00000019', '2025-01-08', '23:27:30', '2025-01-08', 'PEN', 1, NULL, NULL, '1', 186.44, 0.00, 0.00, 0.00, 33.56, 220.00, 'DOSCIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(20, '0101', 4, 1, 2, 2, 'B001', '00000020', '2025-01-09', '14:57:50', '2025-01-09', 'PEN', 2, NULL, NULL, '6', 203.39, 0.00, 0.00, 0.00, 36.61, 240.00, 'DOSCIENTOS CUARENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(21, '0101', 4, 2, 2, 2, 'B001', '00000021', '2025-01-09', '15:00:31', '2025-01-09', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(22, '0101', 4, 3, 2, 2, 'B001', '00000022', '2025-01-09', '20:33:13', '2025-01-10', 'PEN', 1, NULL, NULL, '2', 0.00, 0.00, 0.00, 203.39, 0.00, 203.39, 'DOSCIENTOS TRES Y 39/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(23, '0101', 4, 2, 2, 2, 'B001', '00000023', '2025-01-09', '20:50:28', '2025-01-09', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(24, '0101', 4, 2, 2, 2, 'B001', '00000024', '2025-01-09', '20:51:37', '2025-01-09', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000024, ha sido aceptada'),
(25, '0101', 4, 3, 2, 2, 'B001', '00000025', '2025-01-10', '23:18:40', '2025-01-10', 'PEN', 1, NULL, NULL, '2', 508.47, 0.00, 0.00, 0.00, 91.53, 600.00, 'SEISCIENTOS Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000025, ha sido aceptada'),
(26, '0101', 4, 3, 2, 2, 'B001', '00000026', '2025-01-17', '17:38:43', '2025-01-17', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000026, ha sido aceptada'),
(27, '0101', 4, 3, 2, 2, 'B001', '00000027', '2025-01-17', '19:25:02', '2025-01-17', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000027, ha sido aceptada'),
(28, '0101', 4, 1, 2, 2, 'B001', '00000028', '2025-02-20', '11:43:06', '2025-02-20', 'PEN', 1, NULL, NULL, '1', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'CERO Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(29, '0101', 4, 4, 2, 2, 'B001', '00000029', '2025-02-25', '11:06:30', '2025-02-28', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(30, '0101', 4, 3, 2, 2, 'B001', '00000030', '2025-02-25', '11:22:05', '2025-02-25', 'PEN', 2, NULL, NULL, '1', 313.56, 0.00, 0.00, 0.00, 56.44, 370.00, 'TRESCIENTOS SETENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(31, '0101', 4, 3, 2, 2, 'B001', '00000031', '2025-02-25', '11:22:12', '2025-02-27', 'PEN', 2, NULL, NULL, '1', 313.56, 0.00, 0.00, 0.00, 56.44, 370.00, 'TRESCIENTOS SETENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(32, '0101', 4, 3, 2, 2, 'B001', '00000032', '2025-02-25', '11:22:19', '2025-02-27', 'PEN', 2, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(33, '0101', 4, 4, 2, 2, 'B001', '00000033', '2025-02-25', '11:23:48', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(34, '0101', 4, 4, 2, 2, 'B001', '00000034', '2025-02-25', '12:00:22', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(35, '0101', 4, 4, 2, 2, 'B001', '00000035', '2025-02-24', '12:03:56', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(36, '0101', 4, 4, 2, 2, 'B001', '00000036', '2025-02-24', '12:04:04', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(37, '0101', 4, 4, 2, 2, 'B001', '00000037', '2025-02-24', '12:04:09', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(38, '0101', 4, 4, 2, 2, 'B001', '00000038', '2025-02-25', '12:04:41', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(39, '0101', 4, 4, 2, 2, 'B001', '00000039', '2025-02-25', '12:08:00', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(40, '0101', 4, 1, 2, 2, 'B001', '00000040', '2025-02-24', '12:13:48', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(41, '0101', 4, 4, 2, 2, 'B001', '00000041', '2025-02-25', '12:21:50', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(42, '0101', 4, 2, 2, 2, 'B001', '00000042', '2025-02-25', '12:22:15', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(43, '0101', 4, 2, 2, 2, 'B001', '00000043', '2025-02-25', '12:22:27', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(44, '0101', 4, 2, 2, 2, 'B001', '00000044', '2025-02-25', '12:22:34', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(45, '0101', 4, 4, 2, 2, 'B001', '00000045', '2025-02-25', '12:26:04', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(46, '0101', 4, 4, 2, 2, 'B001', '00000046', '2025-02-25', '12:27:14', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(47, '0101', 4, 4, 2, 2, 'B001', '00000047', '2025-02-25', '12:27:16', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(48, '0101', 4, 4, 2, 2, 'B001', '00000048', '2025-02-25', '12:27:16', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(49, '0101', 4, 4, 2, 2, 'B001', '00000049', '2025-02-25', '12:27:17', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(50, '0101', 4, 4, 2, 2, 'B001', '00000050', '2025-02-25', '12:27:31', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(51, '0101', 4, 4, 2, 2, 'B001', '00000051', '2025-02-25', '12:28:05', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(52, '0101', 4, 4, 2, 2, 'B001', '00000052', '2025-02-25', '12:28:48', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(53, '0101', 4, 2, 2, 2, 'B001', '00000053', '2025-02-27', '09:33:24', '2025-03-05', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(54, '0101', 4, 4, 2, 2, 'B001', '00000054', '2025-02-27', '10:13:56', '2025-03-05', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(55, '0101', 4, 4, 2, 2, 'B001', '00000055', '2025-02-27', '15:53:14', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(56, '0101', 4, 4, 2, 2, 'B001', '00000056', '2025-02-27', '15:53:16', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(57, '0101', 4, 4, 2, 2, 'B001', '00000057', '2025-02-27', '15:53:33', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(58, '0101', 4, 4, 2, 2, 'B001', '00000058', '2025-02-27', '15:53:52', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(59, '0101', 4, 4, 2, 2, 'B001', '00000059', '2025-02-27', '15:54:34', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(60, '0101', 4, 2, 2, 2, 'B001', '00000060', '2025-03-07', '16:18:49', '2025-03-07', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(61, '0101', 4, 4, 2, 2, 'B001', '00000061', '2025-03-08', '10:06:09', '2025-03-21', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(62, '0101', 4, 4, 2, 2, 'B001', '00000062', '2025-03-08', '10:06:33', '2025-03-12', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(63, '0101', 4, 4, 2, 2, 'B001', '00000063', '2025-03-08', '10:06:51', '2025-03-08', 'PEN', 1, NULL, NULL, '1', 440.68, 0.00, 0.00, 0.00, 79.32, 520.00, 'QUINIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(64, '0101', 4, 4, 2, 2, 'B001', '00000064', '2025-03-08', '11:49:13', '2025-03-08', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(65, '0101', 4, 4, 2, 2, 'B001', '00000065', '2025-03-08', '18:11:38', '2025-03-08', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL),
(66, '0101', 4, 3, 2, 2, 'B001', '00000066', '2025-03-08', '18:13:59', '2025-03-08', 'PEN', 2, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(67, '0101', 4, 3, 2, 1, 'F001', '00000001', '2025-03-08', '10:34:26', '2025-03-10', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(68, '0101', 4, 1, 2, 2, 'B001', '00000067', '2025-03-10', '11:01:12', '2025-03-10', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(69, '0101', 4, 2, 2, 2, 'B001', '00000068', '2025-03-10', '11:25:15', '2025-03-10', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(70, '0101', 4, 4, 2, 2, 'B001', '00000069', '2025-03-11', '11:04:31', '2025-03-11', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(71, '0101', 4, 4, 2, 2, 'B001', '00000070', '2025-03-11', '11:58:15', '2025-03-11', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(72, '0101', 4, 3, 2, 2, 'B001', '00000071', '2025-03-13', '09:54:47', '2025-03-13', 'PEN', 1, NULL, NULL, '1', 228.81, 0.00, 0.00, 84.75, 41.19, 354.75, 'TRESCIENTOS CINCUENTA Y CUATRO Y 75/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(73, '0101', 4, 3, 2, 2, 'B001', '00000072', '2025-03-13', '16:32:38', '2025-03-13', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(74, '0101', 4, 1, 2, 2, 'B001', '00000073', '2025-03-17', '13:23:50', '2025-03-17', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(75, '0101', 4, 5, 2, 2, 'B001', '00000074', '2025-03-22', '10:21:54', '2025-03-22', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(76, '0101', 4, 6, 2, 2, 'B001', '00000075', '2025-03-29', '16:30:37', '2025-03-29', 'PEN', 1, NULL, NULL, '1', 440.68, 0.00, 0.00, 0.00, 79.32, 520.00, 'QUINIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL),
(77, '0101', 4, 3, 2, 2, 'B001', '00000076', '2025-03-31', '21:44:23', '2025-03-31', 'PEN', 1, NULL, NULL, '1', 186.44, 0.00, 0.00, 0.00, 33.56, 220.00, 'DOSCIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(78, '0101', 4, 6, 2, 2, 'B001', '00000077', '2025-04-04', '09:15:18', '2025-04-04', 'PEN', 1, NULL, NULL, '1', 440.68, 0.00, 0.00, 0.00, 79.32, 520.00, 'QUINIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(79, '0101', 4, 1, 2, 2, 'B001', '00000078', '2025-04-04', '10:43:20', '2025-04-22', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(80, '0101', 4, 3, 2, 1, 'F001', '00000002', '2025-10-06', '12:13:42', '2025-10-06', 'PEN', 1, NULL, NULL, '1', 2542.37, 0.00, 0.00, 0.00, 457.63, 3000.00, 'TRES MIL Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Factura numero F001-00000002, ha sido aceptada'),
(81, '0101', 4, 3, 2, 1, 'F001', '00000003', '2025-10-06', '12:33:59', '2025-10-06', 'PEN', 1, NULL, NULL, '1', 2542.37, 0.00, 0.00, 0.00, 457.63, 3000.00, 'TRES MIL Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Factura numero F001-00000003, ha sido aceptada'),
(82, '0101', 4, 3, 2, 1, 'F001', '00000004', '2025-10-06', '14:42:16', '2025-10-06', 'PEN', 1, NULL, NULL, '1', 2542.37, 0.00, 0.00, 0.00, 457.63, 3000.00, 'TRES MIL Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Factura numero F001-00000004, ha sido aceptada'),
(83, '0101', 4, 3, 2, 1, 'F001', '00000005', '2025-10-06', '14:45:54', '2025-10-06', 'PEN', 1, NULL, NULL, '1', 2542.37, 0.00, 0.00, 0.00, 457.63, 3000.00, 'TRES MIL Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Factura numero F001-00000005, ha sido aceptada'),
(84, '0101', 4, 3, 2, 1, 'F001', '00000006', '2025-10-06', '14:54:50', '2025-10-06', 'PEN', 1, NULL, NULL, '1', 2542.37, 0.00, 0.00, 0.00, 457.63, 3000.00, 'TRES MIL Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Factura numero F001-00000006, ha sido aceptada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `billingpersale_detail`
--

CREATE TABLE `billingpersale_detail` (
  `id` bigint NOT NULL,
  `sale_id` bigint DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `item` int DEFAULT NULL,
  `unit_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `serie` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT NULL,
  `Type_taxation` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `tax_affectation_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit_value` decimal(10,2) DEFAULT NULL,
  `free_unit_value` decimal(10,2) DEFAULT NULL,
  `item_unit_price` decimal(10,2) DEFAULT NULL,
  `sale_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `billingpersale_detail`
--

INSERT INTO `billingpersale_detail` (`id`, `sale_id`, `product_id`, `quantity`, `item`, `unit_type`, `code`, `description`, `serie`, `tax_percentage`, `Type_taxation`, `tax_amount`, `tax_affectation_type`, `unit_value`, `free_unit_value`, `item_unit_price`, `sale_date`) VALUES
(1, 1, 14, 3, 1, 'NIU', '01', 'Mouse Redragon 011', '978123456789712345', 18.00, 'IGV', 54.92, '10', 101.69, 0.00, 305.08, '2024-10-21'),
(2, 2, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 0.00, 'EXO', 0.00, '20', 101.69, 0.00, 101.69, '2024-10-21'),
(3, 2, 14, 1, 2, 'NIU', '01', 'Mouse Redragon 011', '', 0.00, 'INA', 0.00, '30', 101.69, 0.00, 101.69, '2024-10-21'),
(4, 2, 14, 1, 3, 'NIU', '01', 'Mouse Redragon 011', '', 0.00, 'GRA', 0.00, '21', 0.00, 101.69, 0.00, '2024-10-21'),
(5, 2, 14, 1, 4, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-10-21'),
(6, 3, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '15555', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-10-21'),
(7, 4, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-10-21'),
(8, 5, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(9, 6, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(10, 7, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(11, 8, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(12, 9, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-18'),
(13, 10, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-19'),
(14, 11, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-19'),
(15, 12, 15, 4, 1, 'NIU', '02', 'Teclado 022222', 'B0001', 18.00, 'IGV', 8.54, '10', 11.86, 0.00, 47.46, '2023-11-30'),
(16, 13, 14, 4, 1, 'NIU', '01', 'Mouse Redragon 011', 'B00154', 18.00, 'IGV', 9.15, '10', 12.71, 0.00, 50.85, '2023-12-20'),
(17, 14, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-23'),
(18, 14, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2024-11-23'),
(19, 15, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-23'),
(20, 16, 14, 10, 1, 'NIU', '01', 'Mouse Redragon 011', 'B0001', 18.00, 'IGV', 7.63, '10', 4.24, 0.00, 42.37, '2024-12-25'),
(21, 17, 14, 4, 1, 'NIU', '01', 'Mouse Redragon 011', 'B0020', 18.00, 'IGV', 6.10, '10', 8.47, 0.00, 33.90, '2024-11-30'),
(22, 18, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', 'B000500', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-12-06'),
(23, 19, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-01-08'),
(24, 19, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-08'),
(25, 20, 14, 2, 1, 'NIU', '01', 'Mouse Redragon 011', 'b001', 18.00, 'IGV', 36.61, '10', 101.69, 0.00, 203.39, '2025-01-09'),
(26, 21, 15, 1, 1, 'NIU', '02', 'Teclado 022222', 'b0010', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-09'),
(27, 22, 14, 2, 1, 'NIU', '01', 'Mouse Redragon 011', 'B0025', 0.00, 'INA', 0.00, '30', 101.69, 0.00, 203.39, '2025-01-09'),
(28, 23, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-09'),
(29, 24, 15, 1, 1, 'NIU', '02', 'Teclado 022222', 'B005', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-09'),
(30, 25, 15, 6, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 91.53, '10', 84.75, 0.00, 508.47, '2025-01-10'),
(31, 26, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-17'),
(32, 27, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-17'),
(33, 28, 15, 2, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 0.00, '10', 0.00, 0.00, 0.00, '2025-02-20'),
(34, 29, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(35, 32, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(36, 33, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(37, 34, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(38, 38, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(39, 39, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(40, 40, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-02-24'),
(41, 41, 15, 1, 1, 'NIU', '02', 'Teclado 022222', 'ReDragon', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(42, 44, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(43, 53, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-27'),
(44, 54, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-27'),
(45, 58, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-02-27'),
(46, 59, 18, 1, 1, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-02-27'),
(47, 60, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-07'),
(48, 61, 19, 1, 1, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(49, 62, 18, 1, 1, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(50, 63, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-08'),
(51, 63, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-08'),
(52, 63, 18, 1, 3, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(53, 63, 19, 1, 4, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(54, 64, 19, 1, 1, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(55, 65, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-08'),
(56, 66, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-08'),
(57, 67, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-08'),
(58, 68, 19, 1, 1, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-10'),
(59, 69, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-10'),
(60, 70, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-11'),
(61, 71, 18, 1, 1, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-11'),
(62, 72, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-13'),
(63, 72, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 0.00, 'INA', 0.00, '30', 84.75, 0.00, 84.75, '2025-03-13'),
(64, 72, 19, 1, 3, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-13'),
(65, 73, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-13'),
(66, 74, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-17'),
(67, 75, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', 'as', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-22'),
(68, 76, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-29'),
(69, 76, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-29'),
(70, 76, 18, 1, 3, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-29'),
(71, 76, 19, 1, 4, 'NIU', '04', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-29'),
(72, 77, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-31'),
(73, 77, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-31'),
(74, 78, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-04-04'),
(75, 78, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-04-04'),
(76, 78, 18, 1, 3, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-04-04'),
(77, 78, 19, 1, 4, 'NIU', '04', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-04-04'),
(78, 79, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-04-04'),
(79, 80, 20, 1, 1, 'NIU', '05', 'LAPTOP OFICINA', '', 18.00, 'IGV', 457.63, '10', 2542.37, 0.00, 2542.37, '2025-10-06'),
(80, 81, 20, 1, 1, 'NIU', '05', 'LAPTOP OFICINA', '', 18.00, 'IGV', 457.63, '10', 2542.37, 0.00, 2542.37, '2025-10-06'),
(81, 82, 20, 1, 1, 'NIU', '05', 'LAPTOP OFICINA', '', 18.00, 'IGV', 457.63, '10', 2542.37, 0.00, 2542.37, '2025-10-06'),
(82, 83, 20, 1, 1, 'NIU', '05', 'LAPTOP OFICINA', '', 18.00, 'IGV', 457.63, '10', 2542.37, 0.00, 2542.37, '2025-10-06'),
(83, 84, 20, 1, 1, 'NIU', '05', 'LAPTOP OFICINA', '', 18.00, 'IGV', 457.63, '10', 2542.37, 0.00, 2542.37, '2025-10-06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campus`
--

CREATE TABLE `campus` (
  `id` int NOT NULL,
  `description` varchar(45) NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  `telephone` varchar(15) DEFAULT NULL,
  `address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `campus`
--

INSERT INTO `campus` (`id`, `description`, `status`, `telephone`, `address`) VALUES
(2, 'CALLE 04', 1, NULL, NULL),
(4, 'CALLE 01', 1, NULL, NULL),
(5, 'CALLE 02', 1, NULL, NULL),
(6, 'CALLE 03', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `id_section` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `id_section`, `name`, `status`) VALUES
(1, 1, 'Laptop', 1),
(2, 1, 'Teclado', 1),
(3, 2, 'Micrófonos', 1),
(4, 3, 'Laptops', 1),
(5, 2, 'Monitores', 1),
(6, 1, 'Mouse', 1),
(7, 2, 'Auriculares', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `change_value`
--

CREATE TABLE `change_value` (
  `id` int NOT NULL,
  `id_coins` int NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `purchase_value` decimal(4,3) NOT NULL DEFAULT '0.000',
  `sales_value` decimal(4,3) NOT NULL DEFAULT '0.000',
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `client_queries`
--

CREATE TABLE `client_queries` (
  `id` int NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `client_email` varchar(150) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text,
  `date_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','resolved','archived') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `client_queries`
--

INSERT INTO `client_queries` (`id`, `client_name`, `client_email`, `subject`, `message`, `date_created`, `status`) VALUES
(1, 'Jose Olazbaal', 'jolazabal@gmail.com', 'AAA', 'wsdfd', '2025-09-02 17:54:22', 'pending'),
(2, 'Jose Olazbaal', 'jolazabal@gmail.com', 'AAA', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2025-09-02 17:55:15', 'pending');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coin`
--

CREATE TABLE `coin` (
  `id` int NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `coin`
--

INSERT INTO `coin` (`id`, `code`, `description`, `status`) VALUES
(1, 'PEN', 'Nuevo Sol', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company`
--

CREATE TABLE `company` (
  `id` int NOT NULL,
  `business_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ruc` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `district` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `province` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postal_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `web` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `address2` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `industry` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ubigeo` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `company`
--

INSERT INTO `company` (`id`, `business_name`, `company_name`, `ruc`, `address`, `district`, `province`, `department`, `postal_code`, `phone`, `email`, `web`, `logo`, `country`, `start_date`, `address2`, `industry`, `ubigeo`) VALUES
(1, 'Wilder Florentino Julca Broncano', 'Soluciones Integrales JB SAC', '10410697551', 'Calle Lopez de Zuñiga Nº 547 Piso 2', 'Chancay', 'Huaral', 'Lima', '15131', '996 720 630', 'ventas@solucionesintegralesjb.com', 'www.solucionesintegralesjb.com', 'https://solucionesintegralesjb.com/demo/facturacion/public/app-assets/images/logo/1_673cb3f1dc653.png', 'Perú', '2020-02-15', 'Calle Lopez de Zuñiga Nº 547 - Chancay', 'Ejecución, integración y desarrollo de proyectos. Instalación y mantenimiento de cámaras y equipos de tecnologías en seguridad. Instalación y mantenimiento eléctrico. Soporte técnico en general. sublimación en general. Venta de equipos informáticos, redes, accesorios y materiales eléctricos.', '150605');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `content_headers`
--

CREATE TABLE `content_headers` (
  `id` int NOT NULL,
  `id_product` int NOT NULL,
  `id_header` int NOT NULL,
  `content` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `position` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `content_headers`
--

INSERT INTO `content_headers` (`id`, `id_product`, `id_header`, `content`, `position`) VALUES
(17, 16, 1, NULL, NULL),
(18, 16, 2, NULL, NULL),
(25, 17, 1, NULL, NULL),
(26, 17, 2, NULL, NULL),
(27, 17, 3, NULL, NULL),
(30, 18, 1, NULL, NULL),
(31, 18, 2, NULL, NULL),
(32, 19, 1, NULL, NULL),
(33, 19, 2, NULL, NULL),
(36, 14, 1, NULL, NULL),
(37, 14, 2, NULL, NULL),
(38, 15, 1, 'RGB', 1),
(39, 15, 2, 'SI', 1),
(94, 20, 1, 'Modelo', 1),
(95, 20, 2, 'Lenovo ThinkBook 15 G4', 1),
(96, 20, 1, 'Procesador', 2),
(97, 20, 2, 'Intel Core i5-1240P 12va generación (hasta 4.4 GHz)', 2),
(98, 20, 1, 'Memoria RAM', 3),
(99, 20, 2, '16 GB DDR4 3200 MHz (expandible hasta 40 GB)', 3),
(100, 20, 1, 'Almacenamiento', 4),
(101, 20, 2, '512 GB SSD NVMe M.2', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `creditnote`
--

CREATE TABLE `creditnote` (
  `id` int NOT NULL,
  `id_user` int NOT NULL DEFAULT '0',
  `id_products` int NOT NULL DEFAULT '0',
  `id_sale` int NOT NULL DEFAULT '0',
  `amount` int NOT NULL DEFAULT '0',
  `price_sale` decimal(11,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `correction_description` varchar(50) NOT NULL DEFAULT '0',
  `series` int DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `creditnote`
--

INSERT INTO `creditnote` (`id`, `id_user`, `id_products`, `id_sale`, `amount`, `price_sale`, `discount`, `correction_description`, `series`, `status`) VALUES
(1, 5, 10, 1001, 2, 50.00, 5.00, 'Producto defectuoso', 2024, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta`
--

CREATE TABLE `cuenta` (
  `id` int NOT NULL,
  `id_person` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `recovery_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recovery_expires` datetime DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuenta`
--

INSERT INTO `cuenta` (`id`, `id_person`, `username`, `password`, `email`, `last_login`, `recovery_token`, `recovery_expires`, `status`, `created_at`) VALUES
(1, 1, 'mrtpmitchel', '$2y$10$jUTMF7cw2Y34ageFxL880erIfTof47mD3Gx1N98R0.l3eoOibtgHO', 'mitchel.mrtp@gmail.com', NULL, NULL, NULL, 1, '2025-03-03 11:45:58'),
(2, 2, 'mitchel.mrtp', '$2y$10$synGqPy8tJWmBG9v7G488.wgts6KKTn6kpGyzfPip4/FruAKaFO1m', 'tormit68@gmail.com', NULL, NULL, NULL, 1, '2025-03-04 02:13:41'),
(3, 3, 'nelson', '$2y$10$ZP3lGoiEqAYz5/mztk.9X.nNL650cORuHfvLW2zVcGrcn/UHd5lFq', 'palaciosriverosjosoenelson@gmail.com', NULL, NULL, NULL, 1, '2025-03-13 14:37:45'),
(42, 109, 'jolazabal', '$2y$10$F3fM7Hh5mZbvgbyVoLTKXOnw7ySK.Di9NVMQkETdgnW0FHw4OHmD6', 'olazabalsanchez5@gmail.com', NULL, NULL, NULL, 1, '2025-09-20 17:27:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `development`
--

CREATE TABLE `development` (
  `id` int NOT NULL,
  `id_person` int NOT NULL,
  `id_user` int NOT NULL,
  `date_income` date NOT NULL,
  `id_status_service` int NOT NULL,
  `id_status_delivery` int NOT NULL,
  `id_status_payment` int NOT NULL,
  `name_project` varchar(50) NOT NULL,
  `development_cost` decimal(11,2) NOT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_type`
--

CREATE TABLE `document_type` (
  `id` int NOT NULL,
  `description` varchar(45) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `document_type`
--

INSERT INTO `document_type` (`id`, `description`, `status`) VALUES
(1, 'DNI', 1),
(2, 'RUC', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `headers`
--

CREATE TABLE `headers` (
  `id` int NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `headers`
--

INSERT INTO `headers` (`id`, `name`) VALUES
(1, 'Descripción'),
(2, 'Especificación'),
(3, 'Caracteristicas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `igv`
--

CREATE TABLE `igv` (
  `id` int NOT NULL,
  `value` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `igv`
--

INSERT INTO `igv` (`id`, `value`, `status`) VALUES
(1, 18.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `igvbilling`
--

CREATE TABLE `igvbilling` (
  `id` bigint NOT NULL,
  `operation_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `campus_id` bigint DEFAULT NULL,
  `person_id` bigint DEFAULT NULL,
  `user_id` bigint DEFAULT NULL,
  `voucher_type` int DEFAULT NULL,
  `series` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correlative` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `issue_time` time DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` int DEFAULT NULL,
  `installments` int DEFAULT NULL,
  `installment_amount` decimal(10,2) DEFAULT NULL,
  `payment_medium` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taxable_operations` decimal(10,2) DEFAULT NULL,
  `free_operations` decimal(10,2) DEFAULT NULL,
  `exempt_operations` decimal(10,2) DEFAULT NULL,
  `unaffected_operations` decimal(10,2) DEFAULT NULL,
  `igv` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `leyend` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `retention` decimal(10,2) DEFAULT NULL,
  `retention_percentage` decimal(5,2) DEFAULT NULL,
  `retention_amount` decimal(10,2) DEFAULT NULL,
  `detraction` decimal(10,2) DEFAULT NULL,
  `detraction_percentage` decimal(5,2) DEFAULT NULL,
  `detraction_amount` decimal(10,2) DEFAULT NULL,
  `net_amount_pending_payment` decimal(10,2) DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `response` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `igvbilling`
--

INSERT INTO `igvbilling` (`id`, `operation_type`, `campus_id`, `person_id`, `user_id`, `voucher_type`, `series`, `correlative`, `issue_date`, `issue_time`, `due_date`, `currency`, `payment_method`, `installments`, `installment_amount`, `payment_medium`, `taxable_operations`, `free_operations`, `exempt_operations`, `unaffected_operations`, `igv`, `total_amount`, `leyend`, `retention`, `retention_percentage`, `retention_amount`, `detraction`, `detraction_percentage`, `detraction_amount`, `net_amount_pending_payment`, `status`, `response`) VALUES
(1, '0101', NULL, 1, 2, 2, 'B001', '00000001', '2024-10-21', '10:07:24', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 305.08, 0.00, 0.00, 0.00, 54.92, 360.00, 'TRESCIENTOS SESENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '0101', NULL, 1, 2, 2, 'B001', '00000002', '2024-10-21', '10:16:03', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 101.69, 101.69, 101.69, 101.69, 18.31, 323.38, 'TRESCIENTOS VEINTITRES Y 38/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(3, '0101', 4, 1, 2, 2, 'B001', '00000003', '2024-10-21', '10:19:28', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000003, ha sido aceptada'),
(4, '0101', 5, 1, 2, 2, 'B001', '00000004', '2024-10-21', '11:07:58', '2024-10-21', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(5, '0101', 4, 1, 2, 2, 'B001', '00000005', '2024-11-08', '12:57:29', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000005, ha sido aceptada'),
(6, '0101', 4, 1, 2, 2, 'B001', '00000006', '2024-11-08', '13:47:57', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000006, ha sido aceptada'),
(7, '0101', 4, 1, 2, 2, 'B001', '00000007', '2024-11-08', '14:00:41', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000007, ha sido aceptada'),
(8, '0101', 4, 1, 2, 2, 'B001', '00000008', '2024-11-08', '15:18:22', '2024-11-08', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000008, ha sido aceptada'),
(9, '0101', 4, 1, 2, 2, 'B001', '00000009', '2024-11-18', '10:31:03', '2024-11-18', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000009, ha sido aceptada'),
(10, '0101', 4, 1, 2, 2, 'B001', '00000010', '2024-11-19', '10:36:47', '2024-11-19', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(11, '0101', 4, 1, 2, 2, 'B001', '00000011', '2024-11-19', '23:39:21', '2024-11-19', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000011, ha sido aceptada'),
(12, '0101', 4, 1, 2, 2, 'B001', '00000012', '2023-11-30', '11:06:00', '2024-12-21', 'PEN', 1, NULL, NULL, '1', 47.46, 0.00, 0.00, 0.00, 8.54, 56.00, 'CINCUENTA Y SEIS Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(13, '0101', 4, 1, 2, 2, 'B001', '00000013', '2023-12-20', '14:49:14', '2024-11-21', 'PEN', 1, NULL, NULL, '1', 50.85, 0.00, 0.00, 0.00, 9.15, 60.00, 'SESENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(14, '0101', 4, 1, 2, 2, 'B001', '00000014', '2024-11-23', '20:03:15', '2024-11-23', 'PEN', 1, NULL, NULL, '1', 186.44, 0.00, 0.00, 0.00, 33.56, 220.00, 'DOSCIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(15, '0101', 4, 1, 2, 2, 'B001', '00000015', '2024-11-23', '20:04:09', '2024-11-23', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(16, '0101', 4, 1, 2, 2, 'B001', '00000016', '2024-12-25', '20:43:51', '2024-12-26', 'PEN', 1, NULL, NULL, '1', 42.37, 0.00, 0.00, 0.00, 7.63, 50.00, 'CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000016, ha sido aceptada'),
(17, '0101', 4, 1, 2, 2, 'B001', '00000017', '2024-11-30', '15:10:50', '2024-12-01', 'PEN', 1, NULL, NULL, '1', 33.90, 0.00, 0.00, 0.00, 6.10, 40.00, 'CUARENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000017, ha sido aceptada'),
(18, '0101', 4, 1, 2, 2, 'B001', '00000018', '2024-12-06', '16:38:18', '2024-12-16', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(19, '0101', 4, 1, 2, 2, 'B001', '00000019', '2025-01-08', '23:27:30', '2025-01-08', 'PEN', 1, NULL, NULL, '1', 186.44, 0.00, 0.00, 0.00, 33.56, 220.00, 'DOSCIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(20, '0101', 4, 1, 2, 2, 'B001', '00000020', '2025-01-09', '14:57:50', '2025-01-09', 'PEN', 2, NULL, NULL, '6', 203.39, 0.00, 0.00, 0.00, 36.61, 240.00, 'DOSCIENTOS CUARENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(21, '0101', 4, 2, 2, 2, 'B001', '00000021', '2025-01-09', '15:00:31', '2025-01-09', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(22, '0101', 4, 3, 2, 2, 'B001', '00000022', '2025-01-09', '20:33:13', '2025-01-10', 'PEN', 1, NULL, NULL, '2', 0.00, 0.00, 0.00, 203.39, 0.00, 203.39, 'DOSCIENTOS TRES Y 39/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(23, '0101', 4, 2, 2, 2, 'B001', '00000023', '2025-01-09', '20:50:28', '2025-01-09', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(24, '0101', 4, 2, 2, 2, 'B001', '00000024', '2025-01-09', '20:51:37', '2025-01-09', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000024, ha sido aceptada'),
(25, '0101', 4, 3, 2, 2, 'B001', '00000025', '2025-01-10', '23:18:40', '2025-01-10', 'PEN', 1, NULL, NULL, '2', 508.47, 0.00, 0.00, 0.00, 91.53, 600.00, 'SEISCIENTOS Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000025, ha sido aceptada'),
(26, '0101', 4, 3, 2, 2, 'B001', '00000026', '2025-01-17', '17:38:43', '2025-01-17', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000026, ha sido aceptada'),
(27, '0101', 4, 3, 2, 2, 'B001', '00000027', '2025-01-17', '19:25:02', '2025-01-17', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'La Boleta numero B001-00000027, ha sido aceptada'),
(28, '0101', 4, 1, 2, 2, 'B001', '00000028', '2025-02-20', '11:43:06', '2025-02-20', 'PEN', 1, NULL, NULL, '1', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'CERO Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(29, '0101', 4, 4, 2, 2, 'B001', '00000029', '2025-02-25', '11:06:30', '2025-02-28', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(30, '0101', 4, 3, 2, 2, 'B001', '00000030', '2025-02-25', '11:22:05', '2025-02-25', 'PEN', 2, NULL, NULL, '1', 313.56, 0.00, 0.00, 0.00, 56.44, 370.00, 'TRESCIENTOS SETENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(31, '0101', 4, 3, 2, 2, 'B001', '00000031', '2025-02-25', '11:22:12', '2025-02-27', 'PEN', 2, NULL, NULL, '1', 313.56, 0.00, 0.00, 0.00, 56.44, 370.00, 'TRESCIENTOS SETENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(32, '0101', 4, 3, 2, 2, 'B001', '00000032', '2025-02-25', '11:22:19', '2025-02-27', 'PEN', 2, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(33, '0101', 4, 4, 2, 2, 'B001', '00000033', '2025-02-25', '11:23:48', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(34, '0101', 4, 4, 2, 2, 'B001', '00000034', '2025-02-25', '12:00:22', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(35, '0101', 4, 4, 2, 2, 'B001', '00000035', '2025-02-24', '12:03:56', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(36, '0101', 4, 4, 2, 2, 'B001', '00000036', '2025-02-24', '12:04:04', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(37, '0101', 4, 4, 2, 2, 'B001', '00000037', '2025-02-24', '12:04:09', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(38, '0101', 4, 4, 2, 2, 'B001', '00000038', '2025-02-25', '12:04:41', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(39, '0101', 4, 4, 2, 2, 'B001', '00000039', '2025-02-25', '12:08:00', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(40, '0101', 4, 1, 2, 2, 'B001', '00000040', '2025-02-24', '12:13:48', '2025-03-02', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(41, '0101', 4, 4, 2, 2, 'B001', '00000041', '2025-02-25', '12:21:50', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(42, '0101', 4, 2, 2, 2, 'B001', '00000042', '2025-02-25', '12:22:15', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(43, '0101', 4, 2, 2, 2, 'B001', '00000043', '2025-02-25', '12:22:27', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(44, '0101', 4, 2, 2, 2, 'B001', '00000044', '2025-02-25', '12:22:34', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(45, '0101', 4, 4, 2, 2, 'B001', '00000045', '2025-02-25', '12:26:04', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(46, '0101', 4, 4, 2, 2, 'B001', '00000046', '2025-02-25', '12:27:14', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(47, '0101', 4, 4, 2, 2, 'B001', '00000047', '2025-02-25', '12:27:16', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(48, '0101', 4, 4, 2, 2, 'B001', '00000048', '2025-02-25', '12:27:16', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(49, '0101', 4, 4, 2, 2, 'B001', '00000049', '2025-02-25', '12:27:17', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(50, '0101', 4, 4, 2, 2, 'B001', '00000050', '2025-02-25', '12:27:31', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(51, '0101', 4, 4, 2, 2, 'B001', '00000051', '2025-02-25', '12:28:05', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(52, '0101', 4, 4, 2, 2, 'B001', '00000052', '2025-02-25', '12:28:48', '2025-03-03', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(53, '0101', 4, 2, 2, 2, 'B001', '00000053', '2025-02-27', '09:33:24', '2025-03-05', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(54, '0101', 4, 4, 2, 2, 'B001', '00000054', '2025-02-27', '10:13:56', '2025-03-05', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(55, '0101', 4, 4, 2, 2, 'B001', '00000055', '2025-02-27', '15:53:14', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(56, '0101', 4, 4, 2, 2, 'B001', '00000056', '2025-02-27', '15:53:16', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(57, '0101', 4, 4, 2, 2, 'B001', '00000057', '2025-02-27', '15:53:33', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(58, '0101', 4, 4, 2, 2, 'B001', '00000058', '2025-02-27', '15:53:52', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(59, '0101', 4, 4, 2, 2, 'B001', '00000059', '2025-02-27', '15:54:34', '2025-02-27', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(60, '0101', 4, 2, 2, 2, 'B001', '00000060', '2025-03-07', '16:18:49', '2025-03-07', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(61, '0101', 4, 4, 2, 2, 'B001', '00000061', '2025-03-08', '10:06:09', '2025-03-21', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(62, '0101', 4, 4, 2, 2, 'B001', '00000062', '2025-03-08', '10:06:33', '2025-03-12', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(63, '0101', 4, 4, 2, 2, 'B001', '00000063', '2025-03-08', '10:06:51', '2025-03-08', 'PEN', 1, NULL, NULL, '1', 440.68, 0.00, 0.00, 0.00, 79.32, 520.00, 'QUINIENTOS VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(64, '0101', 4, 4, 2, 2, 'B001', '00000064', '2025-03-08', '11:49:13', '2025-03-08', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(65, '0101', 4, 4, 2, 2, 'B001', '00000065', '2025-03-08', '18:11:38', '2025-03-08', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL),
(66, '0101', 4, 3, 2, 2, 'B001', '00000066', '2025-03-08', '18:13:59', '2025-03-08', 'PEN', 2, NULL, NULL, '1', 101.69, 0.00, 0.00, 0.00, 18.31, 120.00, 'CIENTO VEINTE Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(67, '0101', 4, 3, 2, 1, 'F001', '00000001', '2025-03-08', '10:34:26', '2025-03-10', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(68, '0101', 4, 1, 2, 2, 'B001', '00000067', '2025-03-10', '11:01:12', '2025-03-10', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(69, '0101', 4, 2, 2, 2, 'B001', '00000068', '2025-03-10', '11:25:15', '2025-03-10', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(70, '0101', 4, 4, 2, 2, 'B001', '00000069', '2025-03-11', '11:04:31', '2025-03-11', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'CIEN Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(71, '0101', 4, 4, 2, 2, 'B001', '00000070', '2025-03-11', '11:58:15', '2025-03-11', 'PEN', 1, NULL, NULL, '1', 127.12, 0.00, 0.00, 0.00, 22.88, 150.00, 'CIENTO CINCUENTA Y 00/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(72, '0101', 4, 3, 2, 2, 'B001', '00000071', '2025-04-09', '23:33:14', '2025-04-16', 'PEN', 2, NULL, NULL, '1', 0.00, 0.00, 84.75, 0.00, 0.00, 84.75, 'OCHENTA Y CUATRO Y 75/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(73, '0101', 4, 2, 2, 2, 'B001', '00000072', '2025-04-09', '23:45:21', '2025-04-08', 'PEN', 2, NULL, NULL, '1', 0.00, 0.00, 211.87, 0.00, 0.00, 211.87, 'DOSCIENTOS ONCE Y 87/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(74, '0101', 4, 2, 2, 2, 'B001', '00000073', '2025-04-25', '10:44:44', '2025-04-16', 'PEN', 2, NULL, NULL, '1', 0.00, 0.00, 84.75, 0.00, 0.00, 84.75, 'OCHENTA Y CUATRO Y 75/100 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL),
(87, '0101', 4, 3, 1, 1, 'F001', '00000002', '2025-10-06', '15:04:16', '2025-10-06', 'PEN', 1, NULL, NULL, '1', 84.75, 0.00, 0.00, 0.00, 15.25, 100.00, 'SON: 100.00 SOLES', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `igvbilling_detail`
--

CREATE TABLE `igvbilling_detail` (
  `id` bigint NOT NULL,
  `sale_id` bigint DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `item` int DEFAULT NULL,
  `unit_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `serie` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT NULL,
  `Type_taxation` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `tax_affectation_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit_value` decimal(10,2) DEFAULT NULL,
  `free_unit_value` decimal(10,2) DEFAULT NULL,
  `item_unit_price` decimal(10,2) DEFAULT NULL,
  `sale_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `igvbilling_detail`
--

INSERT INTO `igvbilling_detail` (`id`, `sale_id`, `product_id`, `quantity`, `item`, `unit_type`, `code`, `description`, `serie`, `tax_percentage`, `Type_taxation`, `tax_amount`, `tax_affectation_type`, `unit_value`, `free_unit_value`, `item_unit_price`, `sale_date`) VALUES
(1, 1, 14, 3, 1, 'NIU', '01', 'Mouse Redragon 011', '978123456789712345', 18.00, 'IGV', 54.92, '10', 101.69, 0.00, 305.08, '2024-10-21'),
(2, 2, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 0.00, 'EXO', 0.00, '20', 101.69, 0.00, 101.69, '2024-10-21'),
(3, 2, 14, 1, 2, 'NIU', '01', 'Mouse Redragon 011', '', 0.00, 'INA', 0.00, '30', 101.69, 0.00, 101.69, '2024-10-21'),
(4, 2, 14, 1, 3, 'NIU', '01', 'Mouse Redragon 011', '', 0.00, 'GRA', 0.00, '21', 0.00, 101.69, 0.00, '2024-10-21'),
(5, 2, 14, 1, 4, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-10-21'),
(6, 3, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '15555', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-10-21'),
(7, 4, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-10-21'),
(8, 5, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(9, 6, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(10, 7, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(11, 8, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-08'),
(12, 9, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-18'),
(13, 10, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-19'),
(14, 11, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-19'),
(15, 12, 15, 4, 1, 'NIU', '02', 'Teclado 022222', 'B0001', 18.00, 'IGV', 8.54, '10', 11.86, 0.00, 47.46, '2023-11-30'),
(16, 13, 14, 4, 1, 'NIU', '01', 'Mouse Redragon 011', 'B00154', 18.00, 'IGV', 9.15, '10', 12.71, 0.00, 50.85, '2023-12-20'),
(17, 14, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-23'),
(18, 14, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2024-11-23'),
(19, 15, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-11-23'),
(20, 16, 14, 10, 1, 'NIU', '01', 'Mouse Redragon 011', 'B0001', 18.00, 'IGV', 7.63, '10', 4.24, 0.00, 42.37, '2024-12-25'),
(21, 17, 14, 4, 1, 'NIU', '01', 'Mouse Redragon 011', 'B0020', 18.00, 'IGV', 6.10, '10', 8.47, 0.00, 33.90, '2024-11-30'),
(22, 18, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', 'B000500', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2024-12-06'),
(23, 19, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-01-08'),
(24, 19, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-08'),
(25, 20, 14, 2, 1, 'NIU', '01', 'Mouse Redragon 011', 'b001', 18.00, 'IGV', 36.61, '10', 101.69, 0.00, 203.39, '2025-01-09'),
(26, 21, 15, 1, 1, 'NIU', '02', 'Teclado 022222', 'b0010', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-09'),
(27, 22, 14, 2, 1, 'NIU', '01', 'Mouse Redragon 011', 'B0025', 0.00, 'INA', 0.00, '30', 101.69, 0.00, 203.39, '2025-01-09'),
(28, 23, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-09'),
(29, 24, 15, 1, 1, 'NIU', '02', 'Teclado 022222', 'B005', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-09'),
(30, 25, 15, 6, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 91.53, '10', 84.75, 0.00, 508.47, '2025-01-10'),
(31, 26, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-17'),
(32, 27, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-01-17'),
(33, 28, 15, 2, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 0.00, '10', 0.00, 0.00, 0.00, '2025-02-20'),
(34, 29, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(35, 32, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(36, 33, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(37, 34, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(38, 38, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(39, 39, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(40, 40, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-02-24'),
(41, 41, 15, 1, 1, 'NIU', '02', 'Teclado 022222', 'ReDragon', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(42, 44, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-25'),
(43, 53, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-27'),
(44, 54, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-02-27'),
(45, 58, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-02-27'),
(46, 59, 18, 1, 1, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-02-27'),
(47, 60, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-07'),
(48, 61, 19, 1, 1, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(49, 62, 18, 1, 1, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(50, 63, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-08'),
(51, 63, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-08'),
(52, 63, 18, 1, 3, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(53, 63, 19, 1, 4, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(54, 64, 19, 1, 1, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-08'),
(55, 65, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-08'),
(56, 66, 14, 1, 1, 'NIU', '01', 'Mouse Redragon 011', '', 18.00, 'IGV', 18.31, '10', 101.69, 0.00, 101.69, '2025-03-08'),
(57, 67, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-08'),
(58, 68, 19, 1, 1, 'NIU', '4', 'Auriculares Ryzer', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-10'),
(59, 69, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-10'),
(60, 70, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 18.00, 'IGV', 15.25, '10', 84.75, 0.00, 84.75, '2025-03-11'),
(61, 71, 18, 1, 1, 'NIU', '03', 'MOUSE REDRAGON', '', 18.00, 'IGV', 22.88, '10', 127.12, 0.00, 127.12, '2025-03-11'),
(62, 72, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 0.00, 'EXO', 0.00, '20', 84.75, 0.00, 84.75, '2025-04-09'),
(63, 73, 18, 1, 1, 'NIU', '03', 'MOUSE REDRAGON', '', 0.00, 'EXO', 0.00, '20', 127.12, 0.00, 127.12, '2025-04-09'),
(64, 73, 15, 1, 2, 'NIU', '02', 'Teclado 022222', '', 0.00, 'EXO', 0.00, '20', 84.75, 0.00, 84.75, '2025-04-09'),
(65, 74, 15, 1, 1, 'NIU', '02', 'Teclado 022222', '', 0.00, 'EXO', 0.00, '20', 84.75, 0.00, 84.75, '2025-04-25'),
(78, 87, 0, 1, 1, 'NIU', '005', 'COMPUTADORA', '', 18.00, 'IGV', 15.26, '10', 84.75, 0.00, 84.75, '2025-10-06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `igvinvoice`
--

CREATE TABLE `igvinvoice` (
  `invoice_id` int NOT NULL,
  `user_id` int NOT NULL,
  `client_id` int NOT NULL,
  `voucher_type_code` varchar(18) NOT NULL,
  `series` varchar(4) NOT NULL,
  `correlative` varchar(8) NOT NULL,
  `date_time` date NOT NULL,
  `due_date` date NOT NULL,
  `currency` varchar(45) NOT NULL,
  `payment_type_code` int NOT NULL,
  `tax` decimal(4,2) NOT NULL,
  `taxable_operations` decimal(11,2) NOT NULL,
  `total_igv` decimal(11,2) NOT NULL,
  `total_sale` decimal(11,2) NOT NULL,
  `legend` varchar(100) NOT NULL,
  `document_reason_id` int NOT NULL,
  `support` varchar(45) NOT NULL,
  `related_document` int NOT NULL,
  `unique_voucher` varchar(5) NOT NULL,
  `status` varchar(20) NOT NULL,
  `time` time DEFAULT NULL,
  `exempt_operations` int DEFAULT '0',
  `currency_id` int DEFAULT NULL,
  `assigned_igv` int DEFAULT NULL,
  `unaffected_operations` decimal(11,2) DEFAULT NULL,
  `sunat_response_code` varchar(7) DEFAULT NULL,
  `sunat_response_description` int DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `isc` decimal(11,2) DEFAULT NULL,
  `free_operations` decimal(11,2) DEFAULT NULL,
  `total_discounts` decimal(11,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `igvinvoice`
--

INSERT INTO `igvinvoice` (`invoice_id`, `user_id`, `client_id`, `voucher_type_code`, `series`, `correlative`, `date_time`, `due_date`, `currency`, `payment_type_code`, `tax`, `taxable_operations`, `total_igv`, `total_sale`, `legend`, `document_reason_id`, `support`, `related_document`, `unique_voucher`, `status`, `time`, `exempt_operations`, `currency_id`, `assigned_igv`, `unaffected_operations`, `sunat_response_code`, `sunat_response_description`, `type`, `isc`, `free_operations`, `total_discounts`) VALUES
(5, 1, 2, '01', 'B001', '00000001', '2025-04-08', '2025-04-08', 'PEN', 1, 18.00, 0.00, 0.00, 0.00, 'SON: 0.00 SOLES', 1, 'ELECTRONICO', 0, 'NO', 'PENDIENTE', '11:52:27', 0, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 1, 2, '01', 'B001', '00000001', '2025-04-09', '2025-04-09', 'PEN', 1, 18.00, 0.00, 0.00, 0.00, 'SON: 0.00 SOLES', 1, 'ELECTRONICO', 0, 'NO', 'PENDIENTE', '12:30:44', 0, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 1, 7, '01', 'B001', '00000001', '2025-04-09', '2025-04-09', 'PEN', 1, 18.00, 0.00, 0.00, 0.00, 'SON: 0.00 SOLES', 1, 'ELECTRONICO', 0, 'NO', 'PENDIENTE', '23:43:08', 0, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `igvinvoice_detail`
--

CREATE TABLE `igvinvoice_detail` (
  `invoice_detail_id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `product_code` varchar(45) NOT NULL,
  `product_description` varchar(500) NOT NULL,
  `unit_of_measure` varchar(45) NOT NULL,
  `quantity` int NOT NULL,
  `sale_price` decimal(11,2) NOT NULL,
  `description_correction` varchar(500) DEFAULT NULL,
  `sold_date` datetime DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `other_description` varchar(45) DEFAULT NULL,
  `affectation` varchar(20) DEFAULT NULL,
  `series` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `igvinvoice_detail`
--

INSERT INTO `igvinvoice_detail` (`invoice_detail_id`, `invoice_id`, `product_code`, `product_description`, `unit_of_measure`, `quantity`, `sale_price`, `description_correction`, `sold_date`, `discount`, `other_description`, `affectation`, `series`) VALUES
(81, 5, '123', 'asd', 'NIU', 1, 1.00, NULL, '2025-04-08 00:00:00', 0.00, NULL, 'GRAVADA', NULL),
(82, 6, '1', '3', 'NIU', 1, 1.00, NULL, '2025-04-09 00:00:00', 0.00, NULL, 'GRAVADA', NULL),
(83, 7, '3', '3', 'NIU', 1, 1.00, NULL, '2025-04-09 00:00:00', 0.00, NULL, 'GRAVADA', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `income`
--

CREATE TABLE `income` (
  `id` int NOT NULL,
  `id_person` int NOT NULL,
  `id_user` int NOT NULL,
  `id_voucher_type` int NOT NULL,
  `id_payment_type` int NOT NULL,
  `proof_series` varchar(7) DEFAULT NULL,
  `voucher_series` varchar(10) NOT NULL,
  `date_issue` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_expiration` datetime NOT NULL,
  `igv` decimal(5,2) NOT NULL DEFAULT '0.18',
  `number_installments` int DEFAULT NULL,
  `value_installment` decimal(11,2) DEFAULT NULL,
  `full_purchase` decimal(11,2) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `income`
--

INSERT INTO `income` (`id`, `id_person`, `id_user`, `id_voucher_type`, `id_payment_type`, `proof_series`, `voucher_series`, `date_issue`, `date_expiration`, `igv`, `number_installments`, `value_installment`, `full_purchase`, `status`) VALUES
(55, 1, 2, 9, 3, '12345', '123456', '2025-04-09 23:32:47', '2025-04-30 23:32:00', 0.18, 1234, 1234.00, 240.00, 2),
(56, 2, 24, 9, 1, '45645', '54564', '2025-04-09 23:43:32', '2025-04-10 23:43:00', 0.18, 1, 4.00, 250.00, 2),
(57, 2, 2, 9, 2, '32432', '2423', '2025-04-10 00:16:47', '2025-04-12 00:16:00', 0.18, 2, 3.00, 250.00, 3),
(58, 3, 1, 1, 1, 'B0001', '00001', '2025-05-27 00:44:39', '2025-05-26 23:43:00', 0.18, 1, 0.00, 1500.00, 2),
(59, 1, 1, 9, 1, '2345678', '1234567', '2025-09-02 02:45:19', '2025-09-02 02:45:00', 0.18, 1, 1.00, 240.00, 2),
(60, 1, 2, 2, 1, 'PS001', 'VS999', '2025-09-09 15:39:33', '2025-12-08 00:00:00', 0.18, 12, NULL, 2400.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `income_detail`
--

CREATE TABLE `income_detail` (
  `id` int NOT NULL,
  `id_income` int NOT NULL,
  `id_product` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(11,2) NOT NULL,
  `subtotal` decimal(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `income_detail`
--

INSERT INTO `income_detail` (`id`, `id_income`, `id_product`, `quantity`, `unit_price`, `subtotal`) VALUES
(93, 55, 14, 2, 120.00, 240.00),
(94, 56, 15, 1, 100.00, 100.00),
(95, 56, 18, 1, 150.00, 150.00),
(96, 57, 15, 1, 100.00, 100.00),
(97, 57, 18, 1, 150.00, 150.00),
(98, 58, 14, 10, 150.00, 1500.00),
(99, 59, 14, 2, 120.00, 240.00),
(100, 60, 14, 10, 120.00, 1200.00),
(101, 60, 14, 10, 120.00, 1200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intent`
--

CREATE TABLE `intent` (
  `id` int NOT NULL,
  `token` text NOT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `intent`
--

INSERT INTO `intent` (`id`, `token`, `status`) VALUES
(12, 'gjYSL8sm4porYSQSPo436rnlxTIqTpgfW9jgjnwtfze3caCPGAAZIHGF1n7mlWNvaA863E4TYam55/Pm+LwjiBGPnvSoTQ7QD88mYd5pM4cUpWQgJThJKHGRZL1EsNtsdpBAmg==', 1),
(13, 'gjYSL8sm4porYSQSPo436rnlxTIqTpgfW9jgjnwtfze3caCPGAAZIHGF1n7mlWNvaA863E4TYam55/Pm+LwjiBGPnvSoTQ7QD88mYd5pM4cUpWQgJThJKHGRZL1EsNtsdpBAmg==', 1),
(14, 'gjYSL8sm4porYSQSPo436rnlxTIqTpgfW9jgjnwtfze3caCPGAAZIHGF1n7mlWNvaA863E4TYam55/Pm+LwjiBGPnvSoTQ7QD88mYd5pM4cUpWQgJThJKHGRZL1EsNtsdpBAmg==', 1),
(15, 'gjYSL8sm4porYSQSPo436rnlxTIqTpgfW9jgjnwtfze3caCPGAAZIHGF1n7mlWNvaA863E4TYam55/Pm+LwjiBGPnvSoTQ7QD88mYd5pM4cUpWQgJThJKHGRZL1EsNtsdpBAmg==', 1),
(16, 'EFj4ncJXv2k7BoTw6rZUJ7Qto8w/U2stpqCNZY0boNeX8Q7/noplT8/at/4a55wyFySCmYyf5cN0rDX3c+p9u28OyYSJeeJsyvg7fgbo+3IihvmAWidiivGDJGYoJMywbhIZdA==', 1),
(17, 'RXy0/jSAd5JczrdApFzVgMPPoN9ZJ7RR1JdvuG5bKZ3443zRi8vjrEqYRwkikqQ2fU7BKe3H3A3IACnGLt97aVeRUDl9VL/3hqUx7HSR+EtlAj6HGCZ6TKjLL6bm3GiNuI8MIQ==', 1),
(18, 'RXy0/jSAd5JczrdApFzVgMPPoN9ZJ7RR1JdvuG5bKZ3443zRi8vjrEqYRwkikqQ2fU7BKe3H3A3IACnGLt97aVeRUDl9VL/3hqUx7HSR+EtlAj6HGCZ6TKjLL6bm3GiNuI8MIQ==', 1),
(19, 'VYtGUGue1OCp8/5QBhcIi3ShJbk85/YmVbk3iENr8rIseReWUKYmyZ9BPSmQJflxXaZlVIg62LFTcneW9aJBMVZT6srkr+wXoTGA0pzbPKXKZOPIF+U2dwTS6JX3RDysc12VjA==', 1),
(20, '3ckmMmMWPQfjL1f5lUk3P+kf38KcpJca/H8FExPCtPDZ6qvN/JaaZAMP/yevdj6Kglp/jhDZhTnnjOs88mh6FM8au67U+FLaEFtG5Jktwhs9e0rjGrfCbbLWnhojZWb53P1/Jg==', 1),
(21, 'ao4rLnLR32VGiEXTRJnRDvUa0/YeDi30TSIcPdbYLAdF8SS54edHQXF3yx6rCs3XBfuHr4C04kmqU9XJd5Ya5YlMZSdQDgCZTykvcHIrHGC+QrXzHtu8YLeshLb3W5pmQW5avw==', 1),
(22, '4M2cNr72yhLkmPpw+xXJt82moY7QeBgsAWNznGMkjnIbP3LrxA8OFdi3itOI9y38HC0rsQrgxKnE43AKUpVTTnRM/yUME4sFTUVKX/iWYvsYdkqcfh8P662f+Apoj0/chlz3Og==', 1),
(23, '4LDRDYaAaQu9fMJdJdpk43GN4uDk4tNzN6RZEhYdSJqSXlCSuDNPZA+wqVY5RVTw+qNyOVA+YbNjZrkXDMINumRg1st8sftzpcQvvp57tbDD3077aHoHnOP2CJ+78V8795lA1g==', 1),
(24, 'qQ82xrb5o3w/NUv8+4xU3QLIFSXYmLoFuXE4B8CQGn5vlKZRYBJaVRLyM6go8SAdHb0bSD6w/gARnwrZINKjOwYHjqpb5gTRDYxSsV1gxnzTknpZP2DT7G139Qbvi0uNpXY+6Q==', 1),
(25, 'wwDgSp8/w8HlzFp4ixnFeGaa3QjTF8WqFCDzMjLIMDyDinMVMTjDcmBK7WLGJ1fBtsBsQh4MFZs8YWD1w9IwpWYck99EKXOeHzyZaOqvWzaAvoNO6IiO/Exl5evaBVFqZr2uFw==', 1),
(26, '7pGMixYqPj2pu89F4Jw9niu8H6wSuLt8fX+v8BhTNjVeRWsS0EbZWiOkt1EoDnfktgAfQCUReS2Jmgdr50NTEdDeKzDAH4CYgVBFqxXcsbuzl+4OBBJo+EA/5vks8O2whObWkg==', 1),
(27, '7pGMixYqPj2pu89F4Jw9niu8H6wSuLt8fX+v8BhTNjVeRWsS0EbZWiOkt1EoDnfktgAfQCUReS2Jmgdr50NTEdDeKzDAH4CYgVBFqxXcsbuzl+4OBBJo+EA/5vks8O2whObWkg==', 1),
(28, 'g3zR52fdOK2AX8jrhrDTYt+Iv5z87155ocTCSTb/7xNjnB6nTGgTxhrlComsZ9+4tr3lSUIZhdMKRc+LQRenv8VWt1W0ADwJVzqBH1T6Tm85e2bRzc9JX5Z6EazdbI3w0WnnkA==', 1),
(29, 'g3zR52fdOK2AX8jrhrDTYt+Iv5z87155ocTCSTb/7xNjnB6nTGgTxhrlComsZ9+4tr3lSUIZhdMKRc+LQRenv8VWt1W0ADwJVzqBH1T6Tm85e2bRzc9JX5Z6EazdbI3w0WnnkA==', 1),
(30, 'g3zR52fdOK2AX8jrhrDTYt+Iv5z87155ocTCSTb/7xNjnB6nTGgTxhrlComsZ9+4tr3lSUIZhdMKRc+LQRenv8VWt1W0ADwJVzqBH1T6Tm85e2bRzc9JX5Z6EazdbI3w0WnnkA==', 1),
(31, 'g3zR52fdOK2AX8jrhrDTYt+Iv5z87155ocTCSTb/7xNjnB6nTGgTxhrlComsZ9+4tr3lSUIZhdMKRc+LQRenv8VWt1W0ADwJVzqBH1T6Tm85e2bRzc9JX5Z6EazdbI3w0WnnkA==', 1),
(32, 'g3zR52fdOK2AX8jrhrDTYt+Iv5z87155ocTCSTb/7xNjnB6nTGgTxhrlComsZ9+4tr3lSUIZhdMKRc+LQRenv8VWt1W0ADwJVzqBH1T6Tm85e2bRzc9JX5Z6EazdbI3w0WnnkA==', 1),
(33, 'g3zR52fdOK2AX8jrhrDTYt+Iv5z87155ocTCSTb/7xNjnB6nTGgTxhrlComsZ9+4tr3lSUIZhdMKRc+LQRenv8VWt1W0ADwJVzqBH1T6Tm85e2bRzc9JX5Z6EazdbI3w0WnnkA==', 1),
(34, 'g3zR52fdOK2AX8jrhrDTYt+Iv5z87155ocTCSTb/7xNjnB6nTGgTxhrlComsZ9+4tr3lSUIZhdMKRc+LQRenv8VWt1W0ADwJVzqBH1T6Tm85e2bRzc9JX5Z6EazdbI3w0WnnkA==', 1),
(35, 'hdYHI1wzU3BRu0aA9eft/XO9FewYYlYSJoy7xr3woraDbDYg3nbvuGuN1QdADhRQS9PL1etUycX4qJ9l3OygL/en2dmr+wglqiWjC/93rnx0tx9TH/sEqDb/jBE1v0dKBU92lg==', 1),
(36, 'PBb80w2VO/sHmbUcN017LyzB2WBvodBKdq2u6WlMzSwYUK+IAcnUnSy6R1eullwvGM3tmhFpGYFbyY7Wl22QdZ/uruQRnKbIKHnbsrkqlUSMhNxr78IwFNsw8b3d941IW8u7wA==', 1),
(37, 'PBb80w2VO/sHmbUcN017LyzB2WBvodBKdq2u6WlMzSwYUK+IAcnUnSy6R1eullwvGM3tmhFpGYFbyY7Wl22QdZ/uruQRnKbIKHnbsrkqlUSMhNxr78IwFNsw8b3d941IW8u7wA==', 1),
(38, 'jIqA1t1OztxPyBi+0m5SA5EeNLJqdbd0sTQL63C7qAZv12XCnbk+WV/pxW02XWDtuWTCcTvokUm+e4yTmZ8iSRptR125JgQmyrDUPrqNl0+Od19DUK6yOmKoComdE6nn9umdWw==', 1),
(39, 'hJSY8ZKR3KTqookviNjK3ggchDYuu/aBVJ4/0jbV29KvTCcY1RHPL9OOmRocPkHwe4SwUGwzij+x+mLDMJ4om1CLsqJSjBbyuM6SEAVaQcyIolIJ2VenC4c4VBklDbT6rcaBaA==', 1),
(40, 'au9eplr+9fhg6Tn0LO2lSKEcCI58MVBr78m0XVl8DtkefazbJRcn0qRLnicy6FNnlBCVHaWbzRkiE5JckofhwshsZqlJmv3qAjntsCNc/Z26LOzOW91g/NbP0C6dgef4CCj8kw==', 1),
(41, 'B1dOh6l46moZqheyMzG58h3Ef706I2bNWzzeJlubsLAjRmgGoodJMs9ek0si74Ffjzb+3hcd8Bfw7kVEASC49cseUzTN+bss79vvWK8Wkvj+k7s79S4pHFvvNz2RbT/cXOAYow==', 1),
(42, 'q3o/gZKpnF+CMVg9cejnrMDLJY0WvY8DtRBs7UUVVLONsIskvFqG2fbsHU9m+xVh67M97HNOwy7DVB+Nt5V0yTjOHRyrqD6nxQYckc4rHRAsVqwuSsjFU7e+YHRYNMCp5LB7EA==', 1),
(43, 'QofiOgnDormEW18aKW3fbS9pxy2Jiyr8/7niSHWE3aW1eX4J0dFLIXPyMZdrnDT+FkjBIkyOHQxwNMam84mv8o98X4l0ow/cVkxQ0tC5YeVDH4gCJVrrEqsDwDIW6IOUpOBnCQ==', 1),
(44, 'SV9BqlNiCXkLT3A1fmxPktINZ83dkfyzgl0d2xIiukM11VbfKgPk5Dn6HYS7upLanjrYcVyYP0WSAxaZRtj+Qh6h4gm8W3R2UnIatGoUHdpcURjCHbcHBU6MKvRERU6q5yJsNg==', 1),
(45, 'SV9BqlNiCXkLT3A1fmxPktINZ83dkfyzgl0d2xIiukM11VbfKgPk5Dn6HYS7upLanjrYcVyYP0WSAxaZRtj+Qh6h4gm8W3R2UnIatGoUHdpcURjCHbcHBU6MKvRERU6q5yJsNg==', 1),
(46, 'X8XxMv3yeaEbbJdoxP+le7oPH9DdhWS99uSnjaWhyXdCS8XlmPZDJ/G3totm+yaMsLtnB8V852Q1XN8icAeWcGzPpHcYTmO8wsvYm4w9bx8AuVIJMV6cOAo2DuARtmaHHxBfdA==', 1),
(47, '3ddhc3kUjE96tuGBWDn5hoVPD2RYc9pwUVKjV3KT+Jpm2a50jpBLbC1Ojz6LIpQ4nKr2epT/zFmfZeixqPq5kQL5A8sMWXjTNvnn4RD+Ojch7UIh5zBdUUp4yycqQcsL0unaHg==', 1),
(48, '3ddhc3kUjE96tuGBWDn5hoVPD2RYc9pwUVKjV3KT+Jpm2a50jpBLbC1Ojz6LIpQ4nKr2epT/zFmfZeixqPq5kQL5A8sMWXjTNvnn4RD+Ojch7UIh5zBdUUp4yycqQcsL0unaHg==', 1),
(49, '6kYZOlXPX8G0gQVsJglntTG8W8CtB7TYFOsDeW4hQV1LRZDnn4B35gsTxmGfWLrzQkCJr729VKVLegEXyifXlsKn+4UwqDuO0o8/g+b8FQ8Nt5Yxvbu2FPRFADaxQtrQOfbbzA==', 1),
(50, 'MkTWfTqldeUI2sGH+3baeJB7XCBCkbGnm7P5xpfVZQ0oHPCHpBcaR7VBjGlHA9Q9+DIBlewxJ56yoUUlxARLO1+AsFvCT8D3DzVUBhS8b+/mkNq282mRdUz1TVqzeRdqdNar3A==', 1),
(51, '/OQkyZiYFIYodwkyDHnAxuAuQ21j1+urcB/OSh/MYHNdn6jkqBXSMAe/YYC/wWV937TcwZlZeIFQ8Infm/rjbtzA5YSd7wuulDv7klxyO8WyMeNp6+Zaf4YKK2JOq12dptevpg==', 1),
(52, 'r5y+2I6WzkdaW8XYlNYdrXY0Z4WxAeyGKPBxE10u9Fnnr2D4oprRxgce5zTp0+K7bmOqKbY6vMI2p9VzZwjdesJuDOkqNv2DbfnMb6GXIfnv+1sVCxu/OdEncraocnXEjpkrdg==', 1),
(53, 'Uu/LseiwTcuBuBYZQ4y2DV3WKZDbq7KS6TmLXmndW4yiDtQcKyZ97aAzY7x6egm7QaPkMTW+IL8Ke1LtOtyY3iEmqyjyF0ns3PLz1JtAd6XlLFcnOxbjLP1VppkfaU9fjsEDTQ==', 1),
(54, 'M4a3bQhH4yGE12ne92gyT/TEBb/5aw1nFGT6nGQ/WrDiFizVxDD4aav1UtfPxjPgRMXDhpMwkkZ13cU8plQndGlU/ddX0HlR1YMpKtNCKgYzVObPF0zFxsQgBtCXR7PyPll2zg==', 1),
(55, 'M4a3bQhH4yGE12ne92gyT/TEBb/5aw1nFGT6nGQ/WrDiFizVxDD4aav1UtfPxjPgRMXDhpMwkkZ13cU8plQndGlU/ddX0HlR1YMpKtNCKgYzVObPF0zFxsQgBtCXR7PyPll2zg==', 1),
(56, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(57, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(58, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(59, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(60, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(61, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(62, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(63, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(64, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(65, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(66, '8K+haIUOC90YlTlRfDq3db42wexaVsIlWa2DgePjWjgZKV10t+BsgV2qShRAOAaxG1tekhzBcjCdpRXziWdZiazigK81hR8pvaIv4BoOEzjZyNV3Ji864GM30q0XUvz3XjlP3g==', 1),
(67, 'oNFxN+Z0swj/scML2CPry2Ks8HoIYTyj2ZmTrIXasLKtxRkDeJtdtirEbV5EFvs6l8IiQT4HzzV1+JHsvpGoQm0mhoHzH+lVFmdhKoYey8ueNRrNu/MNMVdxFgiJ5EVCwUWbww==', 1),
(68, 'VT5TCmKo9q8ea0gRUCHDj3PmwksE4pKfWV1f4kDLGkWMSdJkLagHtjuxGhcnEF4nvKQXKENFMA5fZagMog9mpAKqHOH7HI3cSe3uwYO8itcUkRZ+EBEvQ4Js1lDLcIsJ8bkQ0A==', 1),
(69, 'YnfDqsO4VzmtBoXl6yerxUhj+nPHR3HFAjwE1g69kYmo2xZxMRgoMCYhyMmDAP6BBTs/PQvwV2pLZJW7oC6dguNhCpfftV7P+54WCtwwDr6f/EBM2n+QEIO4RmStZyAtiLqxtQ==', 1),
(70, 'UJY44Ff1/hmL/wxHjBlskrlHz364dNvTBkZLNZTAvNln3ZaNtucJUZfkWsXGAkFLPrwSwHl+iOYQsdY+vF8/Y/x4YHJH32qXLXF1d9AcM89lt4AGaz5J2Juh15E9Tv7hnX7PYA==', 1),
(71, 'mcZeASaNoMcIiAOE0PkqT2iis6nx4Jm6k3PnMKn7UZYGeaJBzxI/4EZA3kGn1dVWcj5PptRJWkFkD6ilKL41yh1HIMQeZYAI90FMN6AEPE5mKAi5tCHhrAYpcR1d0AipX0xuyA==', 1),
(72, 'mcZeASaNoMcIiAOE0PkqT2iis6nx4Jm6k3PnMKn7UZYGeaJBzxI/4EZA3kGn1dVWcj5PptRJWkFkD6ilKL41yh1HIMQeZYAI90FMN6AEPE5mKAi5tCHhrAYpcR1d0AipX0xuyA==', 1),
(73, 'mcZeASaNoMcIiAOE0PkqT2iis6nx4Jm6k3PnMKn7UZYGeaJBzxI/4EZA3kGn1dVWcj5PptRJWkFkD6ilKL41yh1HIMQeZYAI90FMN6AEPE5mKAi5tCHhrAYpcR1d0AipX0xuyA==', 1),
(74, 'vnhQe24idfOPji0MdBK6xLX7guvsLukhCTEH9riXWV7+qFDVkVym18J/Po5aZqqXoNku7T8LzuMjkm2L7QMs6lOb7FNjNECckUCEOUp2mfiAZmD4zPYcms7vbL+hXw1o6GV+gA==', 1),
(75, 'oMMr38opMvfurmi11q1sg/Xv63nzc7Slf74erTjRkU6PvtdHrLXWnkcjWnphPDxvKBx2pZWKeWVKsJYPpf5dbJF8IzoHl4aXFOAgVz4dZyFyh4GThOeRY+LBCv98TITX/9INGA==', 1),
(76, '8N+KZ1xjmfnoAtcmII+qNK1Vjyb6P2RTqXUyuIsLx53msYA6SQ1Y6/m70KeFbvOR7yS8VCtj2mDRpB2McDRjPT/ThK5m+RjITWz1eGv5UtpJ4m7Wf/zdd1TKeynfHSheI1/cuQ==', 1),
(77, '8N+KZ1xjmfnoAtcmII+qNK1Vjyb6P2RTqXUyuIsLx53msYA6SQ1Y6/m70KeFbvOR7yS8VCtj2mDRpB2McDRjPT/ThK5m+RjITWz1eGv5UtpJ4m7Wf/zdd1TKeynfHSheI1/cuQ==', 1),
(78, 'vGT6AEh5aqRRLlZeU3He7Sf8V888JPGBk6g74OWLs9OIoQC4Y7gIPs4SUQ0sgbrCCTNGfa1CzCqeLv4E5qygYdn3kJrZ3YoG74IbmvuYQVc2FfkzuKTD4uU15i7oLlsLCcalfQ==', 1),
(79, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(80, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(81, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(82, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(83, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(84, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(85, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(86, 'bOCD16G+b3wPPIMoKtgYOU9O5ZN5TGJmY4srmNs/6FbgQLoFb0G9/KHzECbTRFG+H91kHFNFIc8HENxqAR4nrIb/6qcpRNNgWRar1PME/9p25cC+dfmN4lJdI6j388sdmFiIXA==', 1),
(87, 'tig1oM3GcrAhhDgOhWDTRG1GZnTPykfRvt2B2ZyNc8Drj0wQlC+WKLlYscqp3C5JJG/oVsOjkoY5ELg2L5nR/2j/FcMmZhR3TL0NHHy8KZDnO0iVlRvkMMv7xyph7Q06qQSGPw==', 1),
(88, 'jOGaxNwwfVJCSlUi+haAVcRT3ULIO9lVXeY0NINHq+kwMCC1iLCmKmBKvUQHmfGYhsXEGao/KDW4P57Cok4c1JxSmJgZ4ZGXDQiNtw8rduI4S0KBI35njGmWeRAH8Q2jM93d8A==', 1),
(89, '0JYLib7ARP0wxYx3jpZew7LhuOp/ocVcqPABNmCYsWlSxbrKq0sWZkdwKUHzN9uJhqFhD8i+coiWhMtQdXgga5bHjFE4NFpf+M7rP1DeKlSMNrcw8cNrtQI0UY6Or9EEeKMPQw==', 1),
(90, 'Y5pDuV3KzsbXcnhZ/TbJF1s9mjAtN5V0A+Kk9a1hZQUxqhQ57/P5D5zOp7BstTxGOFMiQ5Hy6i2uWg5wLB/XmlGqbFnpncpOLFJUPKB5/lwR2ZZ5pIh3ieIVTJDE1JCU1cpAKQ==', 1),
(91, 'oBmuG22WuAR7Ak7wIGcQ8fmfaU3ZHUbgOJ7ms/BgT2pkvpGt49TFz0m6X45mpopsBMUcXFWbpnaEb5XW3fLLzNCCWNXAN2A9xt73SoRjoeHoJyBStiqelWE9YWRGbrqhyCGo1w==', 1),
(92, 'lvktEgP+ZaiEGC3x3e5bbxnj/6DYyNQPXnUmKZhtTS5CTg3PyMMCBa6rCVS2zoFFngnQS0vZ2KegrzGrLF1q8wCDMRnqA2BM9utK+/eqPlQ8WH8tZbgKX9d8J8dILyIDkwatbQ==', 1),
(93, 'sJ0Ufe3XKhnRR8zOM02hU//nzaGj2NSXc3ltpdP5jRZPhBXUgv1y/goEv/Nd5Q2rwEpN1AyF6hnjwY4xNqhQt1wUsulKx8R8fX3+5hP9EkE7oSXrqFPGP6OjSsrOGAt1NFmcNA==', 1),
(94, 'q2b/GKLA4xEcbkVcw+09mtaeJyRwDeAghxP0PNUj43uE7Cf08Pw0mSnwmoLu/Zxu/D5axhw2jkzDYqtJLRcLyHmOMkBdiOi4PTHY1HS4I6Vd7V/bmudbgft0VduBzdWt2hypRA==', 1),
(95, '7RPJnNiNp9YULIkgEvCmpz3MhJzuURBGsIcqYKH4GtshtvbrHg5Cxhpps0VMhndxZK+qLy3N+E+L2dP+/kK/ZJyQwH65P6hrH59pAmzxd8NaPPbPSAKKY3lWNyWBFd9yxKXsrQ==', 1),
(96, '/vEyWDGLA28NiTkmG8Jyk0uWWX4tVNLOaqSbbhhbO1tZXwIX0VOv+0O/1q1/0XvYLgH+Qsy6RN8zDowEN5XunRwNSmDi76cJBQzgIduV1lHUfaVO1UAwVin2U4Pbchvfa5lugA==', 1),
(97, '5kkyw1koGvfvFxNHuYV77ur1yRX2hbnzxTqzoWl9BhNemRRw5PZEickGugcAGDpiAGJVvKnp4O7E6ha3iqhtTS/T7uBOuqaxp0sC+Ox+S8KtnxsUFOD+/ggoQ5V/2zKlv32H/A==', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `labels`
--

CREATE TABLE `labels` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `labels`
--

INSERT INTO `labels` (`id`, `name`, `color`) VALUES
(1, 'PROMOCION', '#c20505'),
(2, 'OFERTA', '#d11a23'),
(4, 'CAMPAÑA', '#0b8937'),
(5, 'LIQUIDACION', '#d58920');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `measuring_unit`
--

CREATE TABLE `measuring_unit` (
  `id` int NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `measuring_unit`
--

INSERT INTO `measuring_unit` (`id`, `code`, `description`, `status`) VALUES
(1, 'NIU', 'Unidades(BIENES)', 1),
(2, 'ZZ', 'Unidades(SERVICIOS)', 1),
(3, 'KGM', 'Kilogramos', 1),
(4, 'LBR', 'Libras', 1),
(5, 'GRM', 'Gramos', 1),
(6, 'LTR', 'Litros', 1),
(7, 'MMQ', 'Metros Cubicos', 1),
(8, 'MTR', 'Metros', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

CREATE TABLE `menu` (
  `id` int NOT NULL,
  `description` varchar(80) NOT NULL,
  `icon` varchar(45) DEFAULT NULL,
  `order` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`id`, `description`, `icon`, `order`) VALUES
(1, 'Home', 'home', 1),
(2, 'Productos', 'archive', 2),
(3, 'Almacén', 'box', 3),
(4, 'Ventas', 'shopping-cart', 4),
(5, 'Recepción', 'database', 5),
(6, 'Administración', 'sliders', 6),
(7, 'Servicios', 'settings', 7),
(8, 'Sucursal', 'map', 8),
(9, 'Consulta Compras', 'shopping-bag', 9),
(10, 'Consulta de Ventas', 'shopping-bag', 10),
(11, 'Soporte', 'shield', 11),
(12, 'Carrito', 'shopping-cart', 12),
(13, 'Servicio', 'shield', 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motive_document`
--

CREATE TABLE `motive_document` (
  `id` int NOT NULL,
  `description` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `motive_document`
--

INSERT INTO `motive_document` (`id`, `description`, `status`) VALUES
(1, 'Anulación de la operación', 1),
(2, 'Anulación por error en el RUC', 1),
(3, 'Correción por error en la descripción', 1),
(4, 'Descuento globlal', 1),
(5, 'Descuento por Item', 1),
(6, 'Devolución total', 1),
(7, 'Devolución parcial', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motive_transfer`
--

CREATE TABLE `motive_transfer` (
  `id` int NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `motive_transfer`
--

INSERT INTO `motive_transfer` (`id`, `description`, `status`) VALUES
(1, 'Compra', 1),
(2, 'Consignación', 1),
(3, 'Devolución', 1),
(4, 'Traslado entre almacenes', 1),
(5, 'Venta', 1),
(6, 'Venta con entrega a terceros', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `online_order`
--

CREATE TABLE `online_order` (
  `id` int NOT NULL,
  `operation_type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `issue_date` date NOT NULL,
  `issue_time` time NOT NULL,
  `cliente_id` int NOT NULL,
  `voucher_id` int NOT NULL,
  `series` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `correlative` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `estado_pago` enum('Pendiente','Pagado','Confirmado','Anulado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pendiente',
  `estado_entrega` enum('En Tienda','Enviado','Entregado','Anulado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'En Tienda',
  `coin_id` int NOT NULL,
  `payment_shape` int NOT NULL,
  `igv` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `taxable_amount` decimal(10,2) NOT NULL,
  `taxable_operations` decimal(10,2) DEFAULT NULL,
  `free_operations` decimal(10,2) DEFAULT NULL,
  `exempt_operations` decimal(10,2) DEFAULT NULL,
  `unaffected_operations` decimal(10,2) DEFAULT NULL,
  `leyend` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `document_id` int DEFAULT NULL,
  `auto_cancel_date` date DEFAULT NULL,
  `response` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transfer_reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transfer_date` datetime DEFAULT NULL,
  `transfer_proof` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `online_order`
--

INSERT INTO `online_order` (`id`, `operation_type`, `issue_date`, `issue_time`, `cliente_id`, `voucher_id`, `series`, `correlative`, `total_amount`, `estado_pago`, `estado_entrega`, `coin_id`, `payment_shape`, `igv`, `due_date`, `taxable_amount`, `taxable_operations`, `free_operations`, `exempt_operations`, `unaffected_operations`, `leyend`, `status`, `document_id`, `auto_cancel_date`, `response`, `transfer_reference`, `transfer_date`, `transfer_proof`, `notes`, `user_id`) VALUES
(1, 'VENTA', '2025-09-22', '19:18:27', 109, 1, 'B001', 1, 4850.00, 'Anulado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '123456789', '2025-09-22 00:00:00', 'comprobantes/1758586707_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(2, 'VENTA', '2025-09-23', '11:27:35', 109, 1, 'B001', 2, 4850.00, 'Pendiente', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(3, 'VENTA', '2025-09-23', '11:31:32', 109, 1, 'B001', 3, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '123456789', '2025-09-23 00:00:00', 'comprobantes/1758645092_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(4, 'VENTA', '2025-09-23', '11:31:54', 109, 1, 'B001', 4, 4850.00, 'Pendiente', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(5, 'VENTA', '2025-09-23', '11:32:21', 109, 1, 'B001', 5, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '8949651230', '2025-09-23 00:00:00', 'comprobantes/1758645141_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(6, 'VENTA', '2025-09-23', '11:33:17', 109, 1, 'B001', 6, 4850.00, 'Pendiente', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(7, 'VENTA', '2025-09-23', '11:33:46', 109, 1, 'B001', 7, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '8949651230', '2025-09-23 00:00:00', 'comprobantes/1758645226_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(8, 'VENTA', '2025-09-23', '11:34:02', 109, 1, 'B001', 8, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '8949651230', '2025-09-23 00:00:00', 'comprobantes/1758645242_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(9, 'VENTA', '2025-09-23', '11:34:41', 109, 1, 'B001', 9, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '8949651230', '2025-09-23 00:00:00', 'comprobantes/1758645281_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(10, 'VENTA', '2025-09-23', '11:34:57', 109, 1, 'B001', 10, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '8949651230', '2025-09-23 00:00:00', 'comprobantes/1758645297_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(11, 'VENTA', '2025-09-23', '11:37:35', 109, 1, 'B001', 11, 4850.00, 'Pendiente', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(12, 'VENTA', '2025-09-23', '11:37:51', 109, 1, 'B001', 12, 4850.00, 'Anulado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '8949651230', '2025-09-23 00:00:00', 'comprobantes/1758645471_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(13, 'VENTA', '2025-09-23', '11:48:28', 109, 1, 'B001', 13, 4850.00, 'Pendiente', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(14, 'VENTA', '2025-09-23', '11:48:44', 109, 1, 'B001', 14, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '123456789', '2025-09-23 00:00:00', 'comprobantes/1758646124_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(15, 'VENTA', '2025-09-23', '11:59:53', 109, 1, 'B001', 15, 4850.00, 'Pendiente', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(16, 'VENTA', '2025-09-23', '12:00:09', 109, 1, 'B001', 16, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '12587896', '2025-09-23 00:00:00', 'comprobantes/1758646809_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(17, 'VENTA', '2025-09-23', '12:04:31', 109, 1, 'B001', 17, 4850.00, 'Pendiente', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(18, 'VENTA', '2025-09-23', '12:04:46', 109, 1, 'B001', 18, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(19, 'VENTA', '2025-09-23', '12:05:07', 109, 1, 'B001', 19, 3000.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 3000.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '12587896', '2025-09-23 00:00:00', 'comprobantes/1758647107_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(20, 'VENTA', '2025-09-23', '12:05:25', 109, 1, 'B001', 20, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '12587896', '2025-09-23 00:00:00', 'comprobantes/1758647125_WhatsApp Image 2025-09-20 at 12.50.42.jpeg', '', 1),
(21, 'VENTA', '2025-09-23', '12:05:39', 109, 1, 'B001', 21, 4850.00, 'Anulado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(22, 'VENTA', '2025-09-23', '12:19:35', 109, 1, 'B001', 22, 4850.00, 'Anulado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(23, 'VENTA', '2025-09-23', '12:20:12', 109, 1, 'B001', 23, 3000.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 3000.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '12587896', '2025-09-23 00:00:00', NULL, '', 1),
(24, 'VENTA', '2025-09-23', '12:25:54', 109, 1, 'B001', 24, 4850.00, 'Anulado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(25, 'VENTA', '2025-09-23', '12:28:46', 109, 1, 'B001', 25, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
(26, 'VENTA', '2025-09-23', '12:29:42', 109, 1, 'B001', 26, 4850.00, 'Pagado', 'En Tienda', 1, 1, 18.00, '2025-09-23', 4850.00, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '12587896', '2025-09-23 00:00:00', NULL, '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `online_order_detail`
--

CREATE TABLE `online_order_detail` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `item` int DEFAULT NULL,
  `unit_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `serie` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_percentage` decimal(5,2) DEFAULT NULL,
  `Type_taxation` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT NULL,
  `tax_affectation_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit_value` decimal(10,2) DEFAULT NULL,
  `free_unit_value` decimal(10,2) DEFAULT NULL,
  `item_unit_price` decimal(10,2) NOT NULL,
  `sale_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `status` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `online_order_detail`
--

INSERT INTO `online_order_detail` (`id`, `order_id`, `product_id`, `quantity`, `item`, `unit_type`, `code`, `description`, `serie`, `tax_percentage`, `Type_taxation`, `tax_amount`, `tax_affectation_type`, `unit_value`, `free_unit_value`, `item_unit_price`, `sale_date`, `subtotal`, `status`) VALUES
(1, 1, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(2, 2, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(3, 3, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(4, 4, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(5, 5, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(6, 6, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(7, 7, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(8, 8, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(9, 9, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(10, 10, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(11, 11, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(12, 12, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(13, 13, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(14, 14, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(15, 15, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(16, 16, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(17, 17, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(18, 18, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(19, 19, 20, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3000.00, NULL, 3000.00, 1),
(20, 20, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(21, 21, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 0),
(22, 22, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 0),
(23, 23, 20, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3000.00, NULL, 3000.00, 1),
(24, 24, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 0),
(25, 25, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1),
(26, 26, 19, 1, NULL, 'UND', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4850.00, NULL, 4850.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint NOT NULL,
  `order_id` bigint NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pay`
--

CREATE TABLE `pay` (
  `id` int NOT NULL,
  `id_income` int NOT NULL,
  `value_cuota` decimal(5,2) NOT NULL DEFAULT '0.00',
  `date_pay` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_shape`
--

CREATE TABLE `payment_shape` (
  `id` int NOT NULL,
  `description` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `payment_shape`
--

INSERT INTO `payment_shape` (`id`, `description`, `status`) VALUES
(1, 'Contado', 1),
(2, 'Crédito', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_type`
--

CREATE TABLE `payment_type` (
  `id` int NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `payment_type`
--

INSERT INTO `payment_type` (`id`, `description`, `status`) VALUES
(1, 'Efectivo', 1),
(2, 'Depósito en cuenta', 1),
(3, 'Giro', 1),
(4, 'Transferencia', 1),
(5, 'Orden de pago', 1),
(6, 'Tarjeta de debito', 1),
(7, 'Tarjeta de crédito', 1),
(8, 'Yape', 1),
(9, 'Plin', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permission`
--

CREATE TABLE `permission` (
  `id` int NOT NULL,
  `id_role` int NOT NULL,
  `id_sub_menu` int NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `permission`
--

INSERT INTO `permission` (`id`, `id_role`, `id_sub_menu`, `status`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1),
(6, 1, 6, 1),
(7, 1, 7, 1),
(15, 1, 8, 1),
(16, 1, 9, 1),
(17, 1, 10, 1),
(19, 1, 11, 1),
(20, 1, 12, 1),
(21, 1, 13, 1),
(22, 1, 14, 1),
(23, 1, 15, 1),
(24, 1, 16, 1),
(25, 1, 17, 1),
(26, 1, 18, 1),
(27, 1, 19, 1),
(28, 1, 20, 1),
(29, 1, 21, 1),
(30, 1, 22, 1),
(31, 1, 23, 1),
(32, 1, 24, 1),
(33, 1, 25, 1),
(34, 1, 26, 1),
(35, 1, 27, 1),
(36, 1, 28, 1),
(37, 1, 29, 1),
(38, 1, 30, 1),
(39, 1, 31, 1),
(40, 1, 32, 1),
(41, 1, 33, 1),
(42, 1, 34, 1),
(43, 1, 35, 1),
(44, 1, 36, 1),
(45, 1, 37, 1),
(46, 1, 38, 1),
(47, 1, 39, 1),
(48, 1, 43, 1),
(49, 2, 1, 1),
(50, 2, 2, 1),
(51, 2, 3, 1),
(52, 2, 4, 1),
(53, 2, 18, 1),
(54, 2, 19, 1),
(55, 2, 20, 1),
(56, 2, 43, 1),
(57, 2, 5, 0),
(58, 2, 6, 0),
(59, 2, 7, 0),
(60, 2, 8, 0),
(61, 2, 9, 0),
(62, 2, 10, 0),
(63, 2, 11, 0),
(64, 2, 12, 0),
(65, 2, 13, 0),
(66, 2, 14, 0),
(67, 2, 15, 0),
(68, 2, 16, 0),
(69, 2, 17, 0),
(70, 2, 21, 0),
(71, 2, 22, 0),
(72, 2, 23, 0),
(73, 2, 24, 0),
(74, 2, 25, 0),
(75, 2, 26, 0),
(76, 2, 27, 0),
(77, 2, 28, 0),
(78, 2, 29, 0),
(79, 2, 30, 0),
(80, 2, 31, 0),
(81, 2, 32, 0),
(82, 2, 33, 0),
(83, 2, 34, 0),
(84, 2, 35, 0),
(85, 2, 36, 0),
(86, 2, 37, 0),
(87, 2, 38, 0),
(88, 2, 39, 0),
(89, 3, 22, 1),
(90, 3, 23, 1),
(91, 3, 24, 1),
(92, 3, 1, 0),
(93, 3, 2, 0),
(94, 3, 3, 0),
(95, 3, 4, 0),
(96, 3, 5, 0),
(97, 3, 6, 0),
(98, 3, 7, 0),
(99, 3, 8, 0),
(100, 3, 9, 0),
(101, 3, 10, 0),
(102, 3, 11, 0),
(103, 3, 12, 0),
(104, 3, 13, 0),
(105, 3, 14, 0),
(106, 3, 15, 0),
(107, 3, 16, 0),
(108, 3, 17, 0),
(109, 3, 18, 0),
(110, 3, 19, 0),
(111, 3, 20, 0),
(112, 3, 43, 0),
(113, 3, 21, 0),
(114, 3, 25, 0),
(115, 3, 26, 0),
(116, 3, 27, 0),
(117, 3, 28, 0),
(118, 3, 29, 0),
(119, 3, 30, 0),
(120, 3, 31, 0),
(121, 3, 32, 0),
(122, 3, 33, 0),
(123, 3, 34, 0),
(124, 3, 35, 0),
(125, 3, 36, 0),
(126, 3, 37, 0),
(127, 3, 38, 0),
(128, 3, 39, 0),
(129, 1, 44, 1),
(130, 1, 40, 1),
(131, 2, 44, 1),
(132, 2, 40, 0),
(133, 1, 45, 1),
(134, 1, 46, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `person`
--

CREATE TABLE `person` (
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
  `type_person` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `person`
--

INSERT INTO `person` (`id`, `name`, `document_type_id`, `document_number`, `address`, `phone`, `email`, `contact_person`, `brand`, `license_plate`, `driver_license`, `manager`, `reference`, `status`, `role_person_id`, `type_person`) VALUES
(1, 'VICTOR ENRIQUE VALDEZ PACHECO', 1, '72757455', 'Bellavista 266', '940168728', 'valdezv231@gmail.com', NULL, NULL, NULL, NULL, NULL, '11111', 1, 1, NULL),
(2, 'LEONELLA KEYLA PINTO JARA', 1, '76841363', 'mz d2 lt8', '972040780', 'leonellapj9@gmail.com', NULL, NULL, NULL, NULL, NULL, '', 1, 1, NULL),
(3, 'CORPORACION DE SERVICIOS ESPECIALES JQ S.A.C.', 2, '20531050259', 'AV. SALVADOR DEL SOLAR MZA. G LOTE 04 URB. EL MILAGRO - II ETAPA', '996720630', 'wilderjulca@solucionesintegralesjb.com', NULL, NULL, NULL, NULL, NULL, '', 1, 1, NULL),
(7, 'MARIA GONZALES', 1, '12345678', 'av. la paz 123', '987654321', 'maria@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(8, 'EMPRESA XYZ S.A.C.', 2, '20456789333', 'calle ficticia 456', '123456789', 'contacto@xyz.com', NULL, 'XYZ Brand', NULL, NULL, NULL, NULL, 1, 2, NULL),
(9, 'JUAN PEREZ', 1, '87654321', 'av. siempre viva 789', '912345678', 'juan.perez@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(10, 'COMERCIAL ABC E.I.R.L.', 2, '20567890333', 'jr. ejemplo 321', '321654987', 'info@comercialabc.com', NULL, 'ABC Brand', NULL, NULL, NULL, NULL, 1, 2, NULL),
(11, 'ANA TORRES', 1, '23456789', 'av. los héroes 654', '654321987', 'ana.torres@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(12, 'SERVICIOS INTEGRALES S.R.L.', 2, '20678901333', 'calle servicio 987', '987123456', 'servicios@integrales.com', NULL, 'Servicios Brand', NULL, NULL, NULL, NULL, 1, 2, NULL),
(13, 'LUIS MARTINEZ', 1, '34567890', 'av. la libertad 321', '456789123', 'luis.martinez@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(14, 'SOLUCIONES TECNOLOGICAS S.A.', 2, '20789012333', 'jr. tecnología 654', '789456123', 'info@soltec.com', NULL, 'Soluciones Brand', NULL, NULL, NULL, NULL, 1, 2, NULL),
(15, 'ELENA RUIZ', 1, '45678901', 'av. progreso 987', '321987654', 'elena.ruiz@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(16, 'ANTHONI OTINIANO', 1, '75832762', 'Lima', '921812289', 'otinianoantoni02@gmail.com', NULL, NULL, NULL, NULL, NULL, 'xd', 1, 1, NULL),
(17, 'Jose Olazabal', 1, '72326596', 'Huacho', '992923544', 'olazabalsanchez5@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `code` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_unit` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `id_label` int DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `code`, `id_unit`, `name`, `description`, `price`, `id_label`, `status`) VALUES
(14, '01', 1, 'MOUSE GAMING HALION HA-M529 MONSTER', 'MOUSE', 120.00, 1, 1),
(15, '02', 1, 'Teclado Mecanico', 'PRUEBA', 100.00, 2, 1),
(18, '03', 1, 'MOUSE REDRAGON', 'REDRAGON COBRA FPS', 150.00, 2, 1),
(19, '04', 1, 'Auriculares Ryzer', 'MODEL 115KJ', 4850.00, 5, 1),
(20, '05', 1, 'LAPTOP OFICINA', 'LAPTOP PARA TRABAJOS', 3000.00, 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `id_product` int NOT NULL,
  `image_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `product_images`
--

INSERT INTO `product_images` (`id`, `id_product`, `image_url`) VALUES
(35, 14, 'http://localhost/gliese/public/app-assets/images/product/14/14_6716804738cd7.jpg'),
(36, 15, 'http://localhost/gliese/public/app-assets/images/product/15/15_671bb8d2d693e.jpg'),
(37, 18, 'https://bestmart.cl/cdn/shop/files/426_5058aeb1-4ab3-4dcf-9be9-e1d1a12a260c_800x.jpg?v=1723578708'),
(38, 19, 'https://phantom.pe/media/catalog/product/cache/c58c05327f55128aefac5642661cf3d1/a/u/audifonos_razer_blackshark_v2_hyperspeed_1_.jpg'),
(40, 20, 'http://localhost/gliese/public/app-assets/images/product/20/20_685d6eba51605.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_inventories`
--

CREATE TABLE `product_inventories` (
  `id` int NOT NULL,
  `id_product` int NOT NULL,
  `id_section` int NOT NULL,
  `id_category` int NOT NULL,
  `id_subcategory` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `product_inventories`
--

INSERT INTO `product_inventories` (`id`, `id_product`, `id_section`, `id_category`, `id_subcategory`) VALUES
(7, 14, 1, 6, 1),
(8, 15, 1, 2, 2),
(9, 18, 1, 6, 1),
(10, 19, 2, 7, 3),
(11, 20, 1, 1, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_stock`
--

CREATE TABLE `product_stock` (
  `id` int NOT NULL,
  `id_product` int NOT NULL,
  `stock` int NOT NULL,
  `id_campus` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `product_stock`
--

INSERT INTO `product_stock` (`id`, `id_product`, `stock`, `id_campus`) VALUES
(25, 14, 81, 4),
(26, 15, 47, 4),
(28, 18, 49, 4),
(29, 19, 45, 4),
(31, 20, 45, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_type_sale`
--

CREATE TABLE `product_type_sale` (
  `id` int NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proforma`
--

CREATE TABLE `proforma` (
  `id` int NOT NULL,
  `id_clients` int NOT NULL,
  `id_user` int NOT NULL,
  `id_voucher_type` int NOT NULL,
  `igv` decimal(10,2) NOT NULL DEFAULT '0.00',
  `igv_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `date_issue` date NOT NULL,
  `correlative` varchar(10) NOT NULL,
  `reference` varchar(300) NOT NULL,
  `total_sale` decimal(10,2) NOT NULL DEFAULT '0.00',
  `delivery_time` varchar(50) NOT NULL DEFAULT '',
  `offer_validity` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '2',
  `series_proforma` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `proforma`
--

INSERT INTO `proforma` (`id`, `id_clients`, `id_user`, `id_voucher_type`, `igv`, `igv_total`, `date_issue`, `correlative`, `reference`, `total_sale`, `delivery_time`, `offer_validity`, `status`, `series_proforma`) VALUES
(103, 8, 2, 10, 18.00, 900.00, '2025-04-07', '0000000001', 'Producto nuevo', 5000.00, '24 horas', '15 días', 2, 'P0001'),
(105, 2, 2, 10, 18.00, 45.00, '2025-04-10', '0000000002', 'jkdsnjknas', 250.00, 'ijfsdjflkdsf', 'sjhfjkdnbfjks', 2, 'P0001'),
(106, 2, 2, 10, 18.00, 45.00, '2025-04-10', '0000000003', 'sdasd', 250.00, 'shdas', 'dasdas', 2, 'P0001');

--
-- Disparadores `proforma`
--
DELIMITER $$
CREATE TRIGGER `trg_proforma_autoincrement` BEFORE INSERT ON `proforma` FOR EACH ROW BEGIN
    DECLARE max_correlative BIGINT DEFAULT 9999999999;
    DECLARE last_correlative BIGINT DEFAULT 0;
    DECLARE last_series INT DEFAULT 1;
    DECLARE next_correlative BIGINT;
    DECLARE next_series INT;

    -- Obtener el último registro insertado
    SELECT 
        CAST(correlative AS UNSIGNED),
        CAST(SUBSTRING(series_proforma, 2) AS UNSIGNED)
    INTO 
        last_correlative, last_series
    FROM proforma
    ORDER BY id DESC
    LIMIT 1;

    -- Verificar si se llegó al máximo
    IF last_correlative >= max_correlative THEN
        SET next_correlative = 1;
        SET next_series = last_series + 1;
    ELSE
        SET next_correlative = last_correlative + 1;
        SET next_series = last_series;
    END IF;

    -- Asignar al nuevo registro
    SET NEW.correlative = LPAD(next_correlative, 10, '0');
    SET NEW.series_proforma = CONCAT('P', LPAD(next_series, 4, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proforma_detail`
--

CREATE TABLE `proforma_detail` (
  `id` int NOT NULL,
  `id_products` int NOT NULL,
  `id_proforma` int NOT NULL,
  `amount` int NOT NULL,
  `series` varchar(50) NOT NULL,
  `price_sale` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `proforma_detail`
--

INSERT INTO `proforma_detail` (`id`, `id_products`, `id_proforma`, `amount`, `series`, `price_sale`, `status`) VALUES
(75, 18, 103, 1, '', 127.12, 1),
(76, 19, 103, 1, '', 4110.17, 1),
(77, 14, 104, 1, '', 101.69, 1),
(78, 18, 105, 1, '', 127.12, 1),
(79, 15, 105, 1, '', 84.75, 1),
(80, 18, 106, 1, '', 127.12, 1),
(81, 15, 106, 1, '', 84.75, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_order_history`
--

CREATE TABLE `project_order_history` (
  `id` bigint NOT NULL,
  `project_order_id` bigint NOT NULL,
  `previous_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `new_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `change_date` datetime NOT NULL,
  `changed_by` int NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `referralguide`
--

CREATE TABLE `referralguide` (
  `id` int NOT NULL,
  `id_clients` int NOT NULL,
  `id_sale` int NOT NULL,
  `id_carrier` int NOT NULL,
  `id_reason_transfer` int NOT NULL,
  `date_issue` date NOT NULL,
  `date_transfer` date NOT NULL,
  `modality_transport` varchar(50) NOT NULL,
  `transfer_type` varchar(50) NOT NULL DEFAULT '',
  `gross_weight` int NOT NULL,
  `serie_correlative` varchar(50) NOT NULL,
  `serie_correlative_guide` varchar(50) NOT NULL,
  `address_start` varchar(200) NOT NULL,
  `address_arrival` varchar(200) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `referralguide_detail`
--

CREATE TABLE `referralguide_detail` (
  `id` int NOT NULL,
  `id_products` int NOT NULL,
  `id_referralguide` int NOT NULL,
  `amount` int NOT NULL,
  `series` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role`
--

CREATE TABLE `role` (
  `id` int NOT NULL,
  `description` varchar(45) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `role`
--

INSERT INTO `role` (`id`, `description`, `status`) VALUES
(1, 'ADMINISTRADOR', 1),
(2, 'PRUEBA', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roleperson`
--

CREATE TABLE `roleperson` (
  `id` int NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roleperson`
--

INSERT INTO `roleperson` (`id`, `description`) VALUES
(1, 'Cliente'),
(2, 'Proveedor'),
(3, 'Transportista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sale`
--

CREATE TABLE `sale` (
  `id` int NOT NULL,
  `id_clients` int NOT NULL,
  `id_user` int NOT NULL,
  `id_voucher_type` int NOT NULL,
  `id_coins` int NOT NULL,
  `id_document_reason` int NOT NULL,
  `id_payment_type` int NOT NULL,
  `doc_related` int NOT NULL,
  `series` int DEFAULT NULL,
  `correlative` int DEFAULT NULL,
  `date_issue` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `date_transfer` date DEFAULT NULL,
  `igv` decimal(5,2) NOT NULL DEFAULT '0.00',
  `igv_total` decimal(5,2) NOT NULL DEFAULT '0.00',
  `op_taxed` decimal(5,2) DEFAULT NULL,
  `op_unaffected` decimal(5,2) DEFAULT NULL,
  `op_exonerated` decimal(5,2) DEFAULT NULL,
  `op_free` decimal(5,2) DEFAULT NULL,
  `isc` decimal(5,2) DEFAULT NULL,
  `total_discount` decimal(5,2) DEFAULT NULL,
  `total_sale` decimal(5,2) NOT NULL DEFAULT '0.00',
  `legend` varchar(50) NOT NULL,
  `sustent` varchar(50) NOT NULL,
  `reference` varchar(50) NOT NULL,
  `validity` varchar(50) NOT NULL,
  `time_delivery` varchar(50) NOT NULL,
  `modality_transport` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sale_detail`
--

CREATE TABLE `sale_detail` (
  `id` int NOT NULL,
  `id_sale` int NOT NULL,
  `id_products` int NOT NULL,
  `amount` int NOT NULL,
  `price_sale` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `bestselling_date` date NOT NULL,
  `item` int NOT NULL,
  `series` varchar(50) NOT NULL,
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sections`
--

CREATE TABLE `sections` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sections`
--

INSERT INTO `sections` (`id`, `name`, `status`) VALUES
(1, 'LOGITECH', 1),
(2, 'HALION', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services_area`
--

CREATE TABLE `services_area` (
  `id_areaservice` int NOT NULL,
  `service_area` varchar(50) NOT NULL,
  `servicearea_code` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `services_area`
--

INSERT INTO `services_area` (`id_areaservice`, `service_area`, `servicearea_code`, `state`) VALUES
(1, 'Departamento Ti', '', ''),
(2, 'Consultoria', '', ''),
(3, 'Marketing Digital', '', ''),
(4, 'Ingeneria Electrica', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `service_type`
--

CREATE TABLE `service_type` (
  `id_typeservice` int NOT NULL,
  `type_service` varchar(50) NOT NULL,
  `service_description` varchar(100) DEFAULT NULL,
  `servicearea_code` int NOT NULL,
  `state` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `service_type`
--

INSERT INTO `service_type` (`id_typeservice`, `type_service`, `service_description`, `servicearea_code`, `state`) VALUES
(1, 'Desarrollo de Software', '', 1, ''),
(2, 'Facturacion Electronica', '', 1, ''),
(3, 'Tecnologia en Seguridad', '', 1, ''),
(4, 'Redes e Infraestructura', '', 1, ''),
(5, 'Hosting y Dominio', '', 1, ''),
(6, 'Soporte Tecnico', '', 1, ''),
(7, 'Consultoria Ti', '', 2, ''),
(8, 'Consultoria Empresarial', '', 2, ''),
(9, 'Consultoria Educativa', '', 2, ''),
(10, 'Auditorios', '', 2, ''),
(11, 'Seguridad Informatica', '', 2, ''),
(12, 'Desarrollo Web', '', 3, ''),
(13, 'Posicionamiento Web', '', 3, ''),
(14, 'Merchandising', '', 3, ''),
(15, 'Grafica Publicitaria', '', 3, ''),
(16, 'Redes Sociales', '', 3, ''),
(17, 'Instalaciones Electricas', '', 4, ''),
(18, 'Mantenimiento Electrico', '', 4, ''),
(19, 'Refrigeracion Industrial', '', 4, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_delivery`
--

CREATE TABLE `status_delivery` (
  `id` int NOT NULL,
  `description` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `status_delivery`
--

INSERT INTO `status_delivery` (`id`, `description`, `status`) VALUES
(1, 'Cancelado', 1),
(2, 'Pendiente entrega', 1),
(3, 'Sin servicio', 1),
(4, 'Por servicio', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_payment`
--

CREATE TABLE `status_payment` (
  `id` int NOT NULL,
  `description` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `status_payment`
--

INSERT INTO `status_payment` (`id`, `description`, `status`) VALUES
(1, 'Pendiente pago', 1),
(2, 'Pagado pago', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_service`
--

CREATE TABLE `status_service` (
  `id` int NOT NULL,
  `description` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `status_service`
--

INSERT INTO `status_service` (`id`, `description`, `status`) VALUES
(1, 'Pendiente', 1),
(2, 'Reparación', 1),
(3, 'Terminado', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int NOT NULL,
  `id_category` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategories`
--

INSERT INTO `subcategories` (`id`, `id_category`, `name`, `status`) VALUES
(1, 6, 'Gamer', 1),
(2, 2, 'Gamer', 1),
(3, 7, 'Auriculares Ryzer', 1),
(4, 1, 'Oficina', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sub_menu`
--

CREATE TABLE `sub_menu` (
  `id` int NOT NULL,
  `id_menu` int NOT NULL,
  `description` varchar(45) NOT NULL,
  `icon` varchar(45) DEFAULT NULL,
  `url` varchar(80) NOT NULL,
  `order` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `sub_menu`
--

INSERT INTO `sub_menu` (`id`, `id_menu`, `description`, `icon`, `url`, `order`) VALUES
(1, 1, 'Dashboards', 'circle', 'Dashboards', 1),
(2, 2, 'Lista productos', 'circle', 'Products', 1),
(3, 2, 'Ficha producto', 'circle', 'Productdetails', 2),
(4, 2, 'Etiquetas', 'circle', 'Labels', 3),
(5, 3, 'Secciones', 'circle', 'Sections', 1),
(6, 3, 'Categorias', 'circle', 'Categories', 2),
(7, 3, 'Subcategorias', 'circle', 'Subcategories', 3),
(8, 4, 'Clientes', 'circle', 'Clients', 1),
(9, 4, 'Transportista', 'circle', 'Carrier', 2),
(10, 4, 'Guía de Remisión', 'circle', 'Referralguide', 3),
(11, 4, 'Facturación por venta', 'circle', 'Billingpersale', 4),
(12, 4, 'Pedidos en Linea', 'circle', 'Online_Order', 9),
(13, 4, 'Proforma', 'circle', 'Proforma', 5),
(14, 4, 'Nota de Crédito', 'circle', 'Creditnote', 6),
(15, 4, 'Nota de Venta', 'circle', 'Salenote', 7),
(16, 4, 'Venta a Crédito', 'circle', 'Creditsale', 8),
(17, 5, 'Ingresos', 'circle', 'Income', 1),
(18, 5, 'Proveedores', 'circle', 'Suppliers', 2),
(19, 6, 'Usuarios', 'circle', 'Users', 1),
(20, 6, 'Roles', 'circle', 'Roles', 2),
(21, 6, 'Sedes', 'circle', 'Campus', 3),
(22, 7, 'Facturación con IGV', 'circle', 'Igvinvoicing', 1),
(23, 7, 'Facturación sin IGV', 'circle', 'Igvbilling', 2),
(24, 7, 'Pagos de Servicios', 'circle', 'Payservices', 3),
(25, 7, 'Orden de Pago', 'circle', 'Payorder', 4),
(26, 7, 'Cotización', 'circle', 'Cotize', 5),
(27, 7, 'Servico Desarrollo', 'circle', 'Servicedevelopment', 6),
(28, 7, 'Soporte Técnico', 'circle', 'Supporttechnical', 7),
(29, 7, 'Soporte pago Mensual', 'circle', 'Supportmonthly', 8),
(30, 7, 'Técnicos', 'circle', 'Technicals', 9),
(31, 8, 'Préstamo Productos', 'circle', 'Loanproducts', 1),
(32, 8, 'Consulta de Préstamos', 'circle', 'Loaninquiry', 2),
(33, 9, 'Reporte Compras', 'circle', 'Purchasesreport', 1),
(34, 9, 'Reporte General Compras', 'circle', 'Purchasesreportgeneral', 2),
(35, 10, 'Ventas por Cliente', 'circle', 'Clientssale', 1),
(36, 10, 'Ventas por Usuario', 'circle', 'Usersale', 2),
(37, 10, 'Producto más Vendido', 'circle', 'Productsbestselling', 3),
(38, 10, 'Reporte General Ventas', 'circle', 'Salesgeneralreport', 4),
(39, 10, 'Reporte Venta Mensual', 'circle', 'Salesmonthlyreport', 5),
(40, 11, 'Descarga de TXT', 'circle', 'Downloadtxt', 1),
(43, 6, 'Datos de la Empresa', 'circle', 'Company', 4),
(44, 12, 'Auditoría de pagos', 'circle', 'Shopping', 1),
(45, 13, 'Registro Soporte', 'circle', 'Technicalsupport', 1),
(46, 13, 'Registro Servicio', 'circle', 'old', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sunat`
--

CREATE TABLE `sunat` (
  `id` int NOT NULL,
  `sunat_endpoint` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cert_password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `certificate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sunat`
--

INSERT INTO `sunat` (`id`, `sunat_endpoint`, `cert_password`, `certificate`, `user`, `password`) VALUES
(1, 'FE_BETA', 's01uci0n3sInt3gr1es', 'cert_66fd827091050.p12', 'admin', 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `support`
--

CREATE TABLE `support` (
  `id` int NOT NULL,
  `id_clients` int NOT NULL,
  `id_technical` int NOT NULL,
  `service_code` varchar(50) NOT NULL,
  `service_area` varchar(50) NOT NULL,
  `phone` char(9) DEFAULT NULL,
  `date_income` date NOT NULL,
  `date_departure` date NOT NULL,
  `brand` varchar(50) NOT NULL,
  `problem` varchar(50) NOT NULL,
  `solution` varchar(50) NOT NULL,
  `equipment_type` varchar(50) NOT NULL,
  `support_code` varchar(50) NOT NULL,
  `status_delivery` varchar(50) NOT NULL,
  `status_payment` varchar(50) NOT NULL,
  `status_service` varchar(50) NOT NULL,
  `total` int NOT NULL,
  `cuota` decimal(5,2) NOT NULL DEFAULT '0.00',
  `saldo` decimal(5,2) NOT NULL DEFAULT '0.00',
  `address` varchar(50) NOT NULL,
  `accessory` varchar(50) NOT NULL,
  `recommendation` varchar(50) NOT NULL,
  `warranty` varchar(50) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `support_payment`
--

CREATE TABLE `support_payment` (
  `id` int NOT NULL,
  `id_clients` int NOT NULL,
  `id_user` int NOT NULL,
  `id_support` int NOT NULL,
  `id_payment_type` int NOT NULL,
  `date_pay` date NOT NULL,
  `cuota` decimal(5,2) NOT NULL DEFAULT '0.00',
  `saldo` decimal(5,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `technicals`
--

CREATE TABLE `technicals` (
  `id` int NOT NULL,
  `id_document_type` int DEFAULT NULL,
  `document_number` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '0',
  `phone` varchar(9) NOT NULL DEFAULT '0',
  `area` varchar(50) NOT NULL DEFAULT '0',
  `cargo` varchar(50) NOT NULL DEFAULT '0',
  `technical_type` varchar(50) NOT NULL DEFAULT '0',
  `status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `technicals`
--

INSERT INTO `technicals` (`id`, `id_document_type`, `document_number`, `name`, `phone`, `area`, `cargo`, `technical_type`, `status`) VALUES
(1, 1, '71807058', 'WILDER FLORENTINO JULCA BRONCANO', '924367706', 'Soporte', 'Técnico', 'Tecnico Soporte', 1),
(3, 1, '71807058', 'GIANCARLOS CLAUDIO ORTIZ', '924367706', 'Soporte', 'Técnico', 'Técnico Soporte', 1),
(4, 1, '75232411', 'ALEXANDER DIAZ GRANADOS', '906979126', 'Facturación Electrónica', 'Técnico', 'Técnico Soporte', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `technicalsupport`
--

CREATE TABLE `technicalsupport` (
  `idsupport` int NOT NULL,
  `service_code` varchar(50) NOT NULL,
  `service_area` varchar(50) NOT NULL,
  `client_name` varchar(50) NOT NULL,
  `phone` varchar(12) NOT NULL,
  `entry_date` varchar(50) DEFAULT NULL,
  `exit_date` varchar(50) DEFAULT NULL,
  `responsible_technician` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `problem` varchar(100) DEFAULT NULL,
  `solution` varchar(100) DEFAULT NULL,
  `equipment_type` varchar(100) DEFAULT NULL,
  `delivery_status` varchar(20) NOT NULL,
  `service_status` varchar(20) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `total` int NOT NULL,
  `fee` int NOT NULL,
  `balance` int NOT NULL,
  `address` varchar(50) NOT NULL,
  `accessory` varchar(200) NOT NULL,
  `recommendation` varchar(50) NOT NULL,
  `warranty` varchar(100) NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `reference` varchar(200) NOT NULL,
  `serial_number` varchar(5) DEFAULT NULL,
  `correlative` varchar(8) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `technicalsupport`
--

INSERT INTO `technicalsupport` (`idsupport`, `service_code`, `service_area`, `client_name`, `phone`, `entry_date`, `exit_date`, `responsible_technician`, `brand`, `problem`, `solution`, `equipment_type`, `delivery_status`, `service_status`, `payment_status`, `total`, `fee`, `balance`, `address`, `accessory`, `recommendation`, `warranty`, `service_type`, `reference`, `serial_number`, `correlative`, `state`) VALUES
(1, '231', 'Soporte Tecnico', 'Hola', '123456789', '2025-11-10', '2025-11-10', '3214', 'dawd', 'asdwa', 'adw', 'sad', 'awd', 'Terminado', 'Pagado', 123, 1234, 123, 'sadw', 'sads', 'awdawd', 'dsad', 'Soporte Remoto', 'awdds', 'ST001', '000001', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `token`
--

CREATE TABLE `token` (
  `id` int NOT NULL,
  `token` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `host` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `token`
--

INSERT INTO `token` (`id`, `token`, `host`, `email`, `password`) VALUES
(1, 'apis-token-10307.jNJ6K5RZsRvE9MKBg9ZvfHFmEg7v8nLZ', 'mail.solucionesintegralesjb.com', 'facturacion@solucionesintegralesjb.com', 'N!6zW&amp;amp;skzDy,');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `id_role` int NOT NULL,
  `id_document_type` int NOT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `document_number` varchar(45) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `telephone` varchar(45) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `user` varchar(45) NOT NULL,
  `password` text NOT NULL,
  `image_url` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `id_role`, `id_document_type`, `first_name`, `last_name`, `document_number`, `address`, `telephone`, `email`, `user`, `password`, `image_url`, `status`, `active`) VALUES
(1, 1, 2, 'SolucionesJB', 'Jb', '10410697551', 'Calle López de Zúñiga Nº 547 Piso 2', '996720630', 'soluciones@gmail.com', 'SolucionesJB', '4d4755324d6a67324d6a4d784e5752684d474e695a6d457a5a546b314d546c684d544e6a596a6c6c5932593d', NULL, 1, 1),
(2, 1, 1, 'Diego', 'Uriarte', '74345432', 'Chancay', '996720631', 'grjere698@gmail.com', 'admin', '5a6d4d35597a41334e6a4a6a5a4459784d7a51355a6a457a596d593159324d7a597a566d4d5445784e7a633d', NULL, 1, 1),
(24, 1, 2, 'Seguridad', 'Soluciones integrales', '10410697550', 'Calle Lopez de Zuñiga N°  547 Piso 2', '996720630', 'wilderjulca@solucionesintegralesjb.com', 'seguridad', '4e5749794f444e6d4f5451344d7a68684d6d45344f574e694d7a64684e44526d4d5459334e4445324d54553d', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_campus`
--

CREATE TABLE `user_campus` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `id_campus` int NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `user_campus`
--

INSERT INTO `user_campus` (`id`, `id_user`, `id_campus`, `status`) VALUES
(32, 2, 4, 1),
(33, 2, 5, 1),
(35, 20, 4, 1),
(38, 24, 2, 1),
(39, 24, 4, 1),
(40, 24, 5, 1),
(41, 24, 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `voucher_type`
--

CREATE TABLE `voucher_type` (
  `id` int NOT NULL,
  `code` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `voucher_type`
--

INSERT INTO `voucher_type` (`id`, `code`, `description`, `status`) VALUES
(1, '01', 'Factura', 1),
(2, '03', 'Boleta de Venta', 1),
(3, '07', 'Nota de Credito', 1),
(4, '08', 'Nota de Debito', 1),
(5, '09', 'Guia de Remisión Remitente', 1),
(6, '10', 'Cotización', 1),
(7, '14', 'Orden de Pagos', 1),
(8, '12', 'Ticket', 1),
(9, '13', 'Prestamo', 1),
(10, '06', 'Proforma', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `billingpersale`
--
ALTER TABLE `billingpersale`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `billingpersale_detail`
--
ALTER TABLE `billingpersale_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indices de la tabla `campus`
--
ALTER TABLE `campus`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_section` (`id_section`);

--
-- Indices de la tabla `change_value`
--
ALTER TABLE `change_value`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_coins` (`id_coins`);

--
-- Indices de la tabla `client_queries`
--
ALTER TABLE `client_queries`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `coin`
--
ALTER TABLE `coin`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `content_headers`
--
ALTER TABLE `content_headers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_header` (`id_header`);

--
-- Indices de la tabla `creditnote`
--
ALTER TABLE `creditnote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_products` (`id_products`),
  ADD KEY `id_venta` (`id_sale`);

--
-- Indices de la tabla `cuenta`
--
ALTER TABLE `cuenta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_person` (`id_person`);

--
-- Indices de la tabla `development`
--
ALTER TABLE `development`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_clients` (`id_person`),
  ADD KEY `id_status_service` (`id_status_service`),
  ADD KEY `id_status_delivery` (`id_status_delivery`),
  ADD KEY `id_status_payment` (`id_status_payment`);

--
-- Indices de la tabla `document_type`
--
ALTER TABLE `document_type`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `headers`
--
ALTER TABLE `headers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `igv`
--
ALTER TABLE `igv`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `igvbilling`
--
ALTER TABLE `igvbilling`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `igvbilling_detail`
--
ALTER TABLE `igvbilling_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indices de la tabla `igvinvoice`
--
ALTER TABLE `igvinvoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `igvinvoice_ibfk_1` (`client_id`);

--
-- Indices de la tabla `igvinvoice_detail`
--
ALTER TABLE `igvinvoice_detail`
  ADD PRIMARY KEY (`invoice_detail_id`),
  ADD KEY `fk_invoice_id` (`invoice_id`);

--
-- Indices de la tabla `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_person` (`id_person`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_voucher_type` (`id_voucher_type`),
  ADD KEY `id_payment_type` (`id_payment_type`);

--
-- Indices de la tabla `income_detail`
--
ALTER TABLE `income_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_income` (`id_income`),
  ADD KEY `id_product` (`id_product`);

--
-- Indices de la tabla `intent`
--
ALTER TABLE `intent`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `labels`
--
ALTER TABLE `labels`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `measuring_unit`
--
ALTER TABLE `measuring_unit`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `motive_document`
--
ALTER TABLE `motive_document`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `motive_transfer`
--
ALTER TABLE `motive_transfer`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `online_order`
--
ALTER TABLE `online_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correlative` (`correlative`),
  ADD UNIQUE KEY `unique_series_correlative` (`series`,`correlative`),
  ADD UNIQUE KEY `idx_series_correlative` (`series`,`correlative`),
  ADD KEY `payment_shape` (`payment_shape`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `coin_id` (`coin_id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `fk_online_order_user` (`user_id`);

--
-- Indices de la tabla `online_order_detail`
--
ALTER TABLE `online_order_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `pay`
--
ALTER TABLE `pay`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_income` (`id_income`);

--
-- Indices de la tabla `payment_shape`
--
ALTER TABLE `payment_shape`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `payment_type`
--
ALTER TABLE `payment_type`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_PERMISSION_ROLE` (`id_role`),
  ADD KEY `FK_PERMISSION_SUB_MENU` (`id_sub_menu`);

--
-- Indices de la tabla `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `document_number` (`document_number`),
  ADD KEY `role_person_id` (`role_person_id`),
  ADD KEY `document_type_id` (`document_type_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `id_label` (`id_label`),
  ADD KEY `id_u_medida` (`id_unit`);

--
-- Indices de la tabla `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_product` (`id_product`);

--
-- Indices de la tabla `product_inventories`
--
ALTER TABLE `product_inventories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_section` (`id_section`),
  ADD KEY `id_category` (`id_category`),
  ADD KEY `id_subcategory` (`id_subcategory`);

--
-- Indices de la tabla `product_stock`
--
ALTER TABLE `product_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_campus` (`id_campus`);

--
-- Indices de la tabla `product_type_sale`
--
ALTER TABLE `product_type_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proforma`
--
ALTER TABLE `proforma`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_clients` (`id_clients`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_voucher_type` (`id_voucher_type`);

--
-- Indices de la tabla `proforma_detail`
--
ALTER TABLE `proforma_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_products` (`id_products`),
  ADD KEY `id_proforma` (`id_proforma`);

--
-- Indices de la tabla `referralguide`
--
ALTER TABLE `referralguide`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_clients` (`id_clients`),
  ADD KEY `id_carrier` (`id_carrier`),
  ADD KEY `id_sale` (`id_sale`),
  ADD KEY `id_transfer_type` (`id_reason_transfer`) USING BTREE;

--
-- Indices de la tabla `referralguide_detail`
--
ALTER TABLE `referralguide_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_products` (`id_products`),
  ADD KEY `id_referralguide` (`id_referralguide`);

--
-- Indices de la tabla `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roleperson`
--
ALTER TABLE `roleperson`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sale`
--
ALTER TABLE `sale`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_clients` (`id_clients`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `voucher_type` (`id_voucher_type`),
  ADD KEY `id_coins` (`id_coins`),
  ADD KEY `id_document_reason` (`id_document_reason`),
  ADD KEY `id_payment_type` (`id_payment_type`);

--
-- Indices de la tabla `sale_detail`
--
ALTER TABLE `sale_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sale` (`id_sale`),
  ADD KEY `id_products` (`id_products`);

--
-- Indices de la tabla `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `services_area`
--
ALTER TABLE `services_area`
  ADD PRIMARY KEY (`id_areaservice`);

--
-- Indices de la tabla `service_type`
--
ALTER TABLE `service_type`
  ADD PRIMARY KEY (`id_typeservice`);

--
-- Indices de la tabla `status_delivery`
--
ALTER TABLE `status_delivery`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `status_payment`
--
ALTER TABLE `status_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `status_service`
--
ALTER TABLE `status_service`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_category` (`id_category`);

--
-- Indices de la tabla `sub_menu`
--
ALTER TABLE `sub_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_SUB_MENU_MENU` (`id_menu`);

--
-- Indices de la tabla `sunat`
--
ALTER TABLE `sunat`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `support`
--
ALTER TABLE `support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_clients` (`id_clients`),
  ADD KEY `id_technical` (`id_technical`);

--
-- Indices de la tabla `support_payment`
--
ALTER TABLE `support_payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_clients` (`id_clients`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_support` (`id_support`),
  ADD KEY `id_payment_type` (`id_payment_type`);

--
-- Indices de la tabla `technicals`
--
ALTER TABLE `technicals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_document_type` (`id_document_type`);

--
-- Indices de la tabla `technicalsupport`
--
ALTER TABLE `technicalsupport`
  ADD PRIMARY KEY (`idsupport`);

--
-- Indices de la tabla `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_UNIQUE` (`user`),
  ADD UNIQUE KEY `document_number_UNIQUE` (`document_number`),
  ADD KEY `FK_USER_ROLE` (`id_role`),
  ADD KEY `FK_USER_DOCUMENT_TYPE` (`id_document_type`);

--
-- Indices de la tabla `user_campus`
--
ALTER TABLE `user_campus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_USER_CAMPUS_CAMPUS` (`id_campus`),
  ADD KEY `FK_USER_CAMPUS_USER` (`id_user`);

--
-- Indices de la tabla `voucher_type`
--
ALTER TABLE `voucher_type`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `billingpersale`
--
ALTER TABLE `billingpersale`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT de la tabla `billingpersale_detail`
--
ALTER TABLE `billingpersale_detail`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `campus`
--
ALTER TABLE `campus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `change_value`
--
ALTER TABLE `change_value`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `client_queries`
--
ALTER TABLE `client_queries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `coin`
--
ALTER TABLE `coin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `company`
--
ALTER TABLE `company`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `content_headers`
--
ALTER TABLE `content_headers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `creditnote`
--
ALTER TABLE `creditnote`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuenta`
--
ALTER TABLE `cuenta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `development`
--
ALTER TABLE `development`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `document_type`
--
ALTER TABLE `document_type`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `headers`
--
ALTER TABLE `headers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `igv`
--
ALTER TABLE `igv`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `igvbilling`
--
ALTER TABLE `igvbilling`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `igvbilling_detail`
--
ALTER TABLE `igvbilling_detail`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `igvinvoice`
--
ALTER TABLE `igvinvoice`
  MODIFY `invoice_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `igvinvoice_detail`
--
ALTER TABLE `igvinvoice_detail`
  MODIFY `invoice_detail_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `income`
--
ALTER TABLE `income`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `income_detail`
--
ALTER TABLE `income_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `intent`
--
ALTER TABLE `intent`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT de la tabla `labels`
--
ALTER TABLE `labels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `measuring_unit`
--
ALTER TABLE `measuring_unit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `motive_document`
--
ALTER TABLE `motive_document`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `online_order`
--
ALTER TABLE `online_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `online_order_detail`
--
ALTER TABLE `online_order_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `pay`
--
ALTER TABLE `pay`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_type`
--
ALTER TABLE `payment_type`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT de la tabla `person`
--
ALTER TABLE `person`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `product_inventories`
--
ALTER TABLE `product_inventories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `product_type_sale`
--
ALTER TABLE `product_type_sale`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proforma`
--
ALTER TABLE `proforma`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `proforma_detail`
--
ALTER TABLE `proforma_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT de la tabla `referralguide`
--
ALTER TABLE `referralguide`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `referralguide_detail`
--
ALTER TABLE `referralguide_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `role`
--
ALTER TABLE `role`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `roleperson`
--
ALTER TABLE `roleperson`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sale`
--
ALTER TABLE `sale`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sale_detail`
--
ALTER TABLE `sale_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `services_area`
--
ALTER TABLE `services_area`
  MODIFY `id_areaservice` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `service_type`
--
ALTER TABLE `service_type`
  MODIFY `id_typeservice` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `status_delivery`
--
ALTER TABLE `status_delivery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `status_payment`
--
ALTER TABLE `status_payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `status_service`
--
ALTER TABLE `status_service`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sub_menu`
--
ALTER TABLE `sub_menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `sunat`
--
ALTER TABLE `sunat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `support`
--
ALTER TABLE `support`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `support_payment`
--
ALTER TABLE `support_payment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `technicals`
--
ALTER TABLE `technicals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `technicalsupport`
--
ALTER TABLE `technicalsupport`
  MODIFY `idsupport` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `token`
--
ALTER TABLE `token`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `user_campus`
--
ALTER TABLE `user_campus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `voucher_type`
--
ALTER TABLE `voucher_type`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `igvinvoice_detail`
--
ALTER TABLE `igvinvoice_detail`
  ADD CONSTRAINT `fk_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `igvinvoice` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `igvinvoice_detail_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `igvinvoice` (`invoice_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
