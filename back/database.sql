SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `aplicacionfinalenrira` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `aplicacionfinalenrira`;

CREATE TABLE `comentarios` (
  `ID_comentario` int NOT NULL,
  `ID_usuario` int NOT NULL,
  `ID_podcast` int NOT NULL,
  `contenido` text COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_comentario` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `mensajes` (
  `id` int NOT NULL,
  `id_emisor` int NOT NULL,
  `id_receptor` int NOT NULL,
  `mensaje` text COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notificaciones` (
  `id` int NOT NULL,
  `ID_usuario` int DEFAULT NULL,
  `contenido` text COLLATE utf8mb4_general_ci,
  `fecha_emision` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_reset_tokens` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pending_users` (
  `ID_usuario` int NOT NULL,
  `nombre_usuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `correo_electronico` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rol` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `podcasts` (
  `ID_podcast` int NOT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `fecha_subida` date DEFAULT NULL,
  `archivo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `categoria` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `thumbnail` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `roles` (
  `ID_rol` int NOT NULL,
  `nombre_rol` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `seguimiento` (
  `id_emisor` int NOT NULL,
  `id_receptor` int NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuarios` (
  `ID_usuario` int NOT NULL,
  `nombre_usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `correo_electronico` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `foto_perfil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rol` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`ID_comentario`),
  ADD KEY `fk_usuario` (`ID_usuario`),
  ADD KEY `fk_podcast` (`ID_podcast`);

ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_emisor` (`id_emisor`),
  ADD KEY `id_receptor` (`id_receptor`);

ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ID_usuario` (`ID_usuario`);

ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_password_reset_tokens_users` (`email`);

ALTER TABLE `pending_users`
  ADD PRIMARY KEY (`ID_usuario`),
  ADD UNIQUE KEY `correo_electronico` (`correo_electronico`),
  ADD UNIQUE KEY `token` (`token`);

ALTER TABLE `podcasts`
  ADD PRIMARY KEY (`ID_podcast`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`ID_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id_emisor`,`id_receptor`),
  ADD KEY `id_receptor` (`id_receptor`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID_usuario`),
  ADD UNIQUE KEY `correo_electronico` (`correo_electronico`),
  ADD KEY `rol` (`rol`),
  ADD KEY `nombre_usuario` (`nombre_usuario`);


ALTER TABLE `comentarios`
  MODIFY `ID_comentario` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `mensajes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `notificaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `password_reset_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `pending_users`
  MODIFY `ID_usuario` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `podcasts`
  MODIFY `ID_podcast` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `roles`
  MODIFY `ID_rol` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios`
  MODIFY `ID_usuario` int NOT NULL AUTO_INCREMENT;


ALTER TABLE `comentarios`
  ADD CONSTRAINT `fk_podcast` FOREIGN KEY (`ID_podcast`) REFERENCES `podcasts` (`ID_podcast`),
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID_usuario`);

ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`id_emisor`) REFERENCES `usuarios` (`ID_usuario`),
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`id_receptor`) REFERENCES `usuarios` (`ID_usuario`);

ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID_usuario`);

ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_password_reset_tokens_users` FOREIGN KEY (`email`) REFERENCES `usuarios` (`correo_electronico`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `seguimiento`
  ADD CONSTRAINT `seguimiento_ibfk_1` FOREIGN KEY (`id_emisor`) REFERENCES `usuarios` (`ID_usuario`),
  ADD CONSTRAINT `seguimiento_ibfk_2` FOREIGN KEY (`id_receptor`) REFERENCES `usuarios` (`ID_usuario`);

ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `roles` (`ID_rol`);
COMMIT;
