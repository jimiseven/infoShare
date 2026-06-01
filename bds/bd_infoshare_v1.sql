-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-06-2026 a las 03:45:13
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
-- Base de datos: `bd_infoshare_v1`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_general`
--

CREATE TABLE `auditoria_general` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria_general`
--

INSERT INTO `auditoria_general` (`id`, `usuario_id`, `accion`, `tabla_afectada`, `registro_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Creó ticket', 'tickets', 1, '127.0.0.1', NULL, '2026-05-31 21:38:58'),
(2, 2, 'Actualizó estado', 'tickets', 3, '127.0.0.1', NULL, '2026-05-31 21:38:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_ticket`
--

CREATE TABLE `comentarios_ticket` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `comentario` text NOT NULL,
  `es_interno` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios_ticket`
--

INSERT INTO `comentarios_ticket` (`id`, `ticket_id`, `usuario_id`, `comentario`, `es_interno`, `created_at`) VALUES
(1, 1, 2, 'Se solicitó reinicio del equipo.', 1, '2026-05-31 21:38:58'),
(2, 3, 2, 'Escalado a HQ para revisión técnica.', 1, '2026-05-31 21:38:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metricas_diarias`
--

CREATE TABLE `metricas_diarias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `inbound_calls` int(10) UNSIGNED DEFAULT 0,
  `outbound_calls` int(10) UNSIGNED DEFAULT 0,
  `failed_calls` int(10) UNSIGNED DEFAULT 0,
  `chats` int(10) UNSIGNED DEFAULT 0,
  `emails` int(10) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metricas_diarias`
--

INSERT INTO `metricas_diarias` (`id`, `fecha`, `usuario_id`, `inbound_calls`, `outbound_calls`, `failed_calls`, `chats`, `emails`, `created_at`) VALUES
(1, '2026-05-31', 2, 12, 8, 2, 6, 4, '2026-05-31 21:38:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prioridades`
--

CREATE TABLE `prioridades` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `nivel` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prioridades`
--

INSERT INTO `prioridades` (`id`, `nombre`, `nivel`) VALUES
(1, 'baja', 1),
(2, 'media', 2),
(3, 'alta', 3),
(4, 'critica', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'admin'),
(3, 'gerente'),
(2, 'usuario_normal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tags`
--

INSERT INTO `tags` (`id`, `nombre`) VALUES
(1, 'hardware'),
(4, 'network'),
(2, 'software'),
(3, 'urgent');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_number` varchar(30) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `estado` enum('no_tomado','respondido','cerrado','preguntar') NOT NULL DEFAULT 'no_tomado',
  `estado_info` varchar(255) DEFAULT NULL,
  `problem_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prioridad_id` tinyint(3) UNSIGNED DEFAULT 2,
  `fecha_vencimiento` datetime DEFAULT NULL,
  `sla_horas` int(10) UNSIGNED DEFAULT 24,
  `creado_por` bigint(20) UNSIGNED NOT NULL,
  `asignado_a` bigint(20) UNSIGNED DEFAULT NULL,
  `cerrado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tickets`
--

INSERT INTO `tickets` (`id`, `ticket_number`, `pais`, `phone`, `email`, `estado`, `estado_info`, `problem_name`, `description`, `prioridad_id`, `fecha_vencimiento`, `sla_horas`, `creado_por`, `asignado_a`, `cerrado_por`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'GD202605280509', 'United States', NULL, 'ethan23698@gmail.com', 'respondido', 'Awaiting customer response', 'P370A - Won’t Turn On', 'Device does not power on.', 3, '2026-06-01 21:38:58', 24, 2, 2, NULL, NULL, '2026-05-31 21:38:58', '2026-05-31 21:38:58'),
(2, 'GD202605280006', 'United States', '00319032272423', 'mike@outlook.com', 'respondido', 'Awaiting customer response', 'DB322 - Not Responding', 'Unit freezes randomly.', 2, '2026-06-02 21:38:58', 24, 2, 2, NULL, NULL, '2026-05-31 21:38:58', '2026-05-31 21:38:58'),
(3, 'GD202605270954', 'United Kingdom', NULL, 'stephen_north@sky.com', 'preguntar', 'Needs HQ review', 'Chime is not Connecting', 'Device cannot connect to network.', 4, '2026-06-01 09:38:58', 24, 2, 2, NULL, NULL, '2026-05-31 21:38:58', '2026-05-31 21:38:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_historial`
--

CREATE TABLE `ticket_historial` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `campo_modificado` varchar(50) NOT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_tags`
--

CREATE TABLE `ticket_tags` (
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ticket_tags`
--

INSERT INTO `ticket_tags` (`ticket_id`, `tag_id`) VALUES
(1, 1),
(3, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol_id` tinyint(3) UNSIGNED NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol_id`, `activo`, `created_at`) VALUES
(1, 'Administrador', 'admin@infoshare.com', '$2y$10$adminhashdemo', 1, 1, '2026-05-31 21:38:57'),
(2, 'Juan Soporte', 'juan@infoshare.com', '$2y$10$userhashdemo', 2, 1, '2026-05-31 21:38:57'),
(3, 'Gerente General', 'gerente@infoshare.com', '$2y$10$managerhashdemo', 3, 1, '2026-05-31 21:38:57');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria_general`
--
ALTER TABLE `auditoria_general`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `comentarios_ticket`
--
ALTER TABLE `comentarios_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `metricas_diarias`
--
ALTER TABLE `metricas_diarias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`,`usuario_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `prioridades`
--
ALTER TABLE `prioridades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `prioridad_id` (`prioridad_id`),
  ADD KEY `creado_por` (`creado_por`),
  ADD KEY `cerrado_por` (`cerrado_por`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_asignado` (`asignado_a`),
  ADD KEY `idx_deleted` (`deleted_at`);

--
-- Indices de la tabla `ticket_historial`
--
ALTER TABLE `ticket_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `ticket_tags`
--
ALTER TABLE `ticket_tags`
  ADD PRIMARY KEY (`ticket_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_general`
--
ALTER TABLE `auditoria_general`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `comentarios_ticket`
--
ALTER TABLE `comentarios_ticket`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `metricas_diarias`
--
ALTER TABLE `metricas_diarias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `prioridades`
--
ALTER TABLE `prioridades`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ticket_historial`
--
ALTER TABLE `ticket_historial`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria_general`
--
ALTER TABLE `auditoria_general`
  ADD CONSTRAINT `auditoria_general_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `comentarios_ticket`
--
ALTER TABLE `comentarios_ticket`
  ADD CONSTRAINT `comentarios_ticket_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ticket_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `metricas_diarias`
--
ALTER TABLE `metricas_diarias`
  ADD CONSTRAINT `metricas_diarias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`prioridad_id`) REFERENCES `prioridades` (`id`),
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`asignado_a`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`cerrado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ticket_historial`
--
ALTER TABLE `ticket_historial`
  ADD CONSTRAINT `ticket_historial_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_historial_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ticket_tags`
--
ALTER TABLE `ticket_tags`
  ADD CONSTRAINT `ticket_tags_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
