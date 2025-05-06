<?php
// Conexión a la base de datos
include_once '../credenciales/credenciales.php';

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar modo SQL y zona horaria
$conn->query("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
$conn->query("SET time_zone = '+00:00'");

// Crear la base de datos
$sql = "CREATE DATABASE IF NOT EXISTS aplicacionFinalEnrIra DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
$conn->query($sql);
$conn->select_db("aplicacionFinalEnrIra");

// Iniciar la transacción
$conn->query("START TRANSACTION");

// Crear tablas
// Crear tabla Roles
$sql = "CREATE TABLE IF NOT EXISTS Roles (
    ID_rol INT NOT NULL AUTO_INCREMENT,
    nombre_rol VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
    descripcion VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    PRIMARY KEY (ID_rol),
    UNIQUE KEY nombre_rol (nombre_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Usuarios
$sql = "CREATE TABLE IF NOT EXISTS Usuarios (
    ID_usuario INT NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL,
    correo_electronico VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
    contrasena VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    foto_perfil VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    rol INT DEFAULT NULL,
    PRIMARY KEY (ID_usuario),
    UNIQUE KEY correo_electronico (correo_electronico),
    KEY rol (rol),
    KEY nombre_usuario (nombre_usuario),
    FOREIGN KEY (rol) REFERENCES Roles(ID_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Podcasts
$sql = "CREATE TABLE IF NOT EXISTS Podcasts (
    ID_podcast INT NOT NULL AUTO_INCREMENT,
    titulo VARCHAR(100) COLLATE utf8mb4_general_ci NOT NULL,
    descripcion TEXT COLLATE utf8mb4_general_ci,
    fecha_subida DATE DEFAULT NULL,
    archivo VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    categoria VARCHAR(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    thumbnail TEXT COLLATE utf8mb4_general_ci,
    PRIMARY KEY (ID_podcast)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Seguimiento
$sql = "CREATE TABLE IF NOT EXISTS Seguimiento (
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    estado VARCHAR(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
    PRIMARY KEY (id_emisor, id_receptor),
    KEY id_receptor (id_receptor),
    FOREIGN KEY (id_emisor) REFERENCES Usuarios(ID_usuario),
    FOREIGN KEY (id_receptor) REFERENCES Usuarios(ID_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Mensajes
$sql = "CREATE TABLE IF NOT EXISTS Mensajes (
    id INT NOT NULL AUTO_INCREMENT,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    mensaje TEXT COLLATE utf8mb4_general_ci NOT NULL,
    fecha TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY id_emisor (id_emisor),
    KEY id_receptor (id_receptor),
    FOREIGN KEY (id_emisor) REFERENCES Usuarios(ID_usuario),
    FOREIGN KEY (id_receptor) REFERENCES Usuarios(ID_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Notificaciones
$sql = "CREATE TABLE IF NOT EXISTS Notificaciones (
    id INT NOT NULL AUTO_INCREMENT,
    ID_usuario INT DEFAULT NULL,
    contenido TEXT COLLATE utf8mb4_general_ci,
    fecha_emision TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ID_usuario (ID_usuario),
    FOREIGN KEY (ID_usuario) REFERENCES Usuarios(ID_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Comentarios
$sql = "CREATE TABLE IF NOT EXISTS comentarios (
    ID_comentario INT NOT NULL AUTO_INCREMENT,
    ID_usuario INT NOT NULL,
    ID_podcast INT NOT NULL,
    contenido TEXT COLLATE utf8mb4_general_ci NOT NULL,
    fecha_comentario DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ID_comentario),
    KEY fk_usuario (ID_usuario),
    KEY fk_podcast (ID_podcast),
    FOREIGN KEY (ID_usuario) REFERENCES Usuarios(ID_usuario),
    FOREIGN KEY (ID_podcast) REFERENCES Podcasts(ID_podcast)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Usuarios Pendientes
$sql = "CREATE TABLE IF NOT EXISTS pending_users (
    ID_usuario INT NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    correo_electronico VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    contrasena VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    foto_perfil VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    rol INT NOT NULL,
    token VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ID_usuario),
    UNIQUE KEY correo_electronico (correo_electronico),
    UNIQUE KEY token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear tabla Password Reset Tokens
$sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
    token VARCHAR(64) COLLATE utf8mb4_general_ci NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY fk_password_reset_tokens_users (email),
    FOREIGN KEY (email) REFERENCES Usuarios(correo_electronico) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($sql);

// Crear evento para eliminar tokens caducados
$sql = "CREATE EVENT IF NOT EXISTS eliminar_tokens_expirados
        ON SCHEDULE EVERY 1 DAY
        DO DELETE FROM password_reset_tokens WHERE created_at < NOW() - INTERVAL 1 DAY";
$conn->query($sql);

// Insertar el rol "Administrador"
$sql = "INSERT INTO Roles (nombre_rol, descripcion) VALUES ('Administrador', 'Rol con todos los permisos')";
$conn->query($sql);

// Insertar usuario admin "Susurros Vegetales"
$rol_id = 1;
$nombre_usuario = "Susurros Vegetales";
$correo_electronico = "susurrosvegetales4@gmail.com";
$contraseña = password_hash("admin", PASSWORD_DEFAULT);

$sql = "INSERT INTO Usuarios (nombre_usuario, correo_electronico, contrasena, rol) 
        VALUES ('$nombre_usuario', '$correo_electronico', '$contraseña', '$rol_id')";
$conn->query($sql);

// Insertar mensaje de bienvenida
$sql = "INSERT INTO Notificaciones (ID_usuario, contenido) 
        VALUES (1, '¡Bienvenid@ a Susurros Vegetales!')";
$conn->query($sql);

// Confirmar la transacción
$conn->query("COMMIT");

// Cerrar conexión
$conn->close();
?>
