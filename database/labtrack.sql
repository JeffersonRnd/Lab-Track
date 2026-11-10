-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-11-2026 a las 23:39:00
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
-- Base de datos: `labtrack`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `codigo_inventario` varchar(30) NOT NULL,
  `categoria` enum('Cómputo','Electrónica','Redes') NOT NULL,
  `estado` enum('Disponible','Prestado') NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `nombre`, `codigo_inventario`, `categoria`, `estado`) VALUES
(1, 'Laptop Dell Latitude 5420', 'CMP-001', 'Cómputo', 'Disponible'),
(2, 'Laptop HP ProBook 450', 'CMP-002', 'Cómputo', 'Disponible'),
(3, 'Monitor LG 24 pulgadas', 'CMP-003', 'Cómputo', 'Disponible'),
(4, 'Raspberry Pi 4 Model B', 'CMP-004', 'Cómputo', 'Disponible'),
(5, 'Osciloscopio Rigol DS1054Z', 'ELE-001', 'Electrónica', 'Disponible'),
(6, 'Multimetro Fluke 117', 'ELE-002', 'Electrónica', 'Disponible'),
(7, 'Fuente de poder DC 30V', 'ELE-003', 'Electrónica', 'Disponible'),
(8, 'Generador de funciones', 'ELE-004', 'Electrónica', 'Disponible'),
(9, 'Router Cisco 2901', 'RED-001', 'Redes', 'Disponible'),
(10, 'Switch Cisco Catalyst 2960', 'RED-002', 'Redes', 'Disponible'),
(11, 'Kit de crimpado de red', 'RED-003', 'Redes', 'Disponible'),
(12, 'Access Point Ubiquiti UniFi', 'RED-004', 'Redes', 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `equipo_id` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada','Devuelta') NOT NULL DEFAULT 'Pendiente',
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  `fecha_devolucion` datetime DEFAULT NULL,
  `penalizada` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('estudiante','encargado') NOT NULL DEFAULT 'estudiante',
  `estado` enum('Activo','Penalizado') NOT NULL DEFAULT 'Activo',
  `intentos_fallidos` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `codigo`, `nombre`, `password`, `rol`, `estado`, `intentos_fallidos`, `bloqueado_hasta`, `creado_en`) VALUES
(1, 'ADMIN01', 'Encargado de Laboratorio', '$2y$10$sx8.SqqFfq/Ft/qjQ1Jo6.Yg3XMJBTRjx7gW/5l26lk91maXz5age', 'encargado', 'Activo', 0, NULL, '2026-11-10 18:27:16'),
(2, '2021001', 'Carlos Ramirez Torres', '$2y$10$KuLzIv5Idnh3DIMJG.h4CeaBVI2ReUi5EOYSlSNe5aEjw5J89qSOm', 'estudiante', 'Activo', 0, NULL, '2026-11-10 18:27:16'),
(3, '2021002', 'Lucia Fernandez Soto', '$2y$10$KuLzIv5Idnh3DIMJG.h4CeaBVI2ReUi5EOYSlSNe5aEjw5J89qSOm', 'estudiante', 'Activo', 0, NULL, '2026-11-10 18:27:16'),
(4, '2021003', 'Miguel Quispe Huaman', '$2y$10$KuLzIv5Idnh3DIMJG.h4CeaBVI2ReUi5EOYSlSNe5aEjw5J89qSOm', 'estudiante', 'Activo', 0, '2026-11-10 13:50:35', '2026-11-10 18:27:16'),
(5, 'n00247055', 'Angel Renato Cortez Diaz', '$2y$10$9mfoLFlMWmWRc/HqMOZ.L.alrJfTm4uSIuVvAcE77IoCYTcSIXDyK', 'estudiante', 'Activo', 0, NULL, '2026-11-10 22:38:38'),
(6, 'n00373217', 'Ketty Milagros Cueva Marin', '$2y$10$E8j0W0FpeytXY28cAVjpDu1IfMJKsOiTocnzfHJs0kfM.6QTcZAe.', 'estudiante', 'Activo', 0, NULL, '2026-11-10 22:38:38'),
(7, 'n00368981', 'Mauricio Javier Escobar Castrejon', '$2y$10$SU5IqLDafguPL084Zm6hx.YmZfA3y5IzTXVc5RHwwmqMsbkcwpsRG', 'estudiante', 'Activo', 0, NULL, '2026-11-10 22:38:38'),
(8, 'n00355503', 'Luis Eduardo Herrera Ramirez', '$2y$10$vc82WpDnGiTjUyYtN1KZuuQI9.8MPnci2zsYr.RJ.ne2PpyZZhusO', 'estudiante', 'Activo', 0, NULL, '2026-11-10 22:38:38'),
(9, 'n00345124', 'Angel Jefferson Bazan Avelino', '$2y$10$sl.BF9xeZz5T8497IiVkMefPW7r6Q8B70wvmiHn3KYxVdljljQjFe', 'estudiante', 'Activo', 0, NULL, '2026-11-10 22:38:38');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_inventario` (`codigo_inventario`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_res_equipo` (`equipo_id`,`estado`,`fecha_inicio`,`fecha_fin`),
  ADD KEY `idx_res_usuario` (`usuario_id`,`estado`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_res_equipo` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_res_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
