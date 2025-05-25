<?php
// Conexión a la base de datos
include_once '../credenciales/credenciales.php';

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear la base de datos
$sql = "CREATE DATABASE IF NOT EXISTS aplicacionfinalenrira";
$conn->query($sql);
$conn->select_db("aplicacionfinalenrira");

// Limpieza de tablas (si existen)
$conn->query("DROP TABLE IF EXISTS password_reset_tokens");
$conn->query("DROP TABLE IF EXISTS seguimiento");
$conn->query("DROP TABLE IF EXISTS mensajes");
$conn->query("DROP TABLE IF EXISTS notificaciones");
$conn->query("DROP TABLE IF EXISTS podcasts");
$conn->query("DROP TABLE IF EXISTS comentarios");
$conn->query("DROP TABLE IF EXISTS notificaciones");
$conn->query("DROP TABLE IF EXISTS usuarios");
$conn->query("DROP TABLE IF EXISTS roles");

// Crear tabla roles
$sql = "CREATE TABLE roles (
    ID_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255)
)";
$conn->query($sql);

// Crear tabla usuarios con correo único
$sql = "CREATE TABLE usuarios (
    ID_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255),
    rol INT,
    FOREIGN KEY (rol) REFERENCES roles(ID_rol)
)";
$conn->query($sql);

// Crear tabla Podcasts
$sql = "CREATE TABLE IF NOT EXISTS podcasts (
  `ID_podcast` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `fecha_subida` date DEFAULT NULL,
  `archivo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `categoria` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `thumbnail` text COLLATE utf8mb4_general_ci
)";
$conn->query($sql);

// Crear tabla Seguimiento
$sql = "CREATE TABLE seguimiento (
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    estado VARCHAR(20),
    PRIMARY KEY (id_emisor, id_receptor),
    FOREIGN KEY (id_emisor) REFERENCES usuarios(ID_usuario),
    FOREIGN KEY (id_receptor) REFERENCES usuarios(ID_usuario)
)";
$conn->query($sql);

// Crear tabla mensajes
$sql = "CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_emisor) REFERENCES usuarios(ID_usuario),
    FOREIGN KEY (id_receptor) REFERENCES usuarios(ID_usuario)
)";
$conn->query($sql);

// Crear tabla notificaciones
$sql = "CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ID_usuario INT,
    contenido TEXT,
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_usuario) REFERENCES usuarios(ID_usuario)
)";
$conn->query($sql);

// Crear tabla comentarios
$sql = "CREATE TABLE comentarios (
    ID_comentario INT AUTO_INCREMENT PRIMARY KEY,
    ID_usuario INT NOT NULL,
    ID_podcast INT NOT NULL,
    contenido TEXT NOT NULL,
    fecha_comentario DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_usuario) REFERENCES usuarios(ID_usuario),
    FOREIGN KEY (ID_podcast) REFERENCES podcasts(ID_podcast)
)";
$conn->query($sql);

// Crear tabla usuarios Pendientes con correo único
$sql = "CREATE TABLE pending_users (
    ID_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(255) NOT NULL,
    correo_electronico VARCHAR(255) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255),
    rol INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Crear tabla Password Reset Tokens
$sql = "CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES usuarios(correo_electronico) ON DELETE CASCADE
)";
$conn->query($sql);

// Evento para eliminar tokens caducados (después de 24h)
$sql = "CREATE EVENT IF NOT EXISTS eliminar_tokens_expirados
        ON SCHEDULE EVERY 1 DAY
        DO DELETE FROM password_reset_tokens WHERE created_at < NOW() - INTERVAL 1 DAY";
$conn->query($sql);

// Insertar el rol "Administrador"
$sql = "INSERT INTO roles (nombre_rol, descripcion) VALUES ('Administrador', 'Rol con todos los permisos')";
$conn->query($sql);
// Insertar el rol "Usuario"
$sql = "INSERT INTO roles (nombre_rol, descripcion) VALUES ('Usuario', 'Rol con permisos limitados')";
$conn->query($sql);

// Insertar usuario admin "Susurros Vegetales"
$rol_id = 1;
$nombre_usuario = "Susurros Vegetales";
$correo_electronico = "susurrosvegetales4@gmail.com";
$contraseña = password_hash("Admin", PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena, rol) 
        VALUES ('$nombre_usuario', '$correo_electronico', '$contraseña', '$rol_id')";
$conn->query($sql);

// Insertar mensaje de bienvenida
$sql = "INSERT INTO notificaciones (ID_usuario, contenido) 
        VALUES (1, '¡Bienvenid@ a Susurros Vegetales!')";
$conn->query($sql);

// Cerrar conexión
$conn->close();
?>
