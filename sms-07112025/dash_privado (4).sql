-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-11-2025 a las 22:01:16
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dash_privado`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saving_users`
--

CREATE TABLE `saving_users` (
  `id` int(11) NOT NULL,
  `phone_number` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `saving` varchar(20) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sms_credentials`
--

CREATE TABLE `sms_credentials` (
  `id` int(11) NOT NULL,
  `apiKey` varchar(255) NOT NULL,
  `apiSecret` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sms_credentials`
--

INSERT INTO `sms_credentials` (`id`, `apiKey`, `apiSecret`) VALUES
(1, 'mz7Y5j47vK', 'a7fcgcxbme');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `message` varchar(160) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `saving_users`
--
ALTER TABLE `saving_users`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sms_credentials`
--
ALTER TABLE `sms_credentials`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `saving_users`
--
ALTER TABLE `saving_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sms_credentials`
--
ALTER TABLE `sms_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
