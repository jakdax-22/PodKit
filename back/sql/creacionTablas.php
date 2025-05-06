<?php
// Conexión a la base de datos
include_once '../credenciales/credenciales.php';

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear la base de datos
$sql = "CREATE DATABASE IF NOT EXISTS aplicacionFinalEnrIra";
$conn->query($sql);
$conn->select_db("aplicacionFinalEnrIra");

// Limpieza de tablas (si existen)
$conn->query("DROP TABLE IF EXISTS password_reset_tokens");
$conn->query("DROP TABLE IF EXISTS Seguimiento");
$conn->query("DROP TABLE IF EXISTS Mensajes");
$conn->query("DROP TABLE IF EXISTS Notificaciones");
$conn->query("DROP TABLE IF EXISTS comentarios");
$conn->query("DROP TABLE IF EXISTS Podcasts");
$conn->query("DROP TABLE IF EXISTS Usuarios");
$conn->query("DROP TABLE IF EXISTS Roles");

// Crear tabla Roles
$sql = "CREATE TABLE Roles (
    ID_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255)
)";
$conn->query($sql);

// Crear tabla Usuarios con correo único
$sql = "CREATE TABLE Usuarios (
    ID_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255),
    rol INT,
    FOREIGN KEY (rol) REFERENCES Roles(ID_rol)
)";
$conn->query($sql);

// Crear tabla Podcasts
$sql = "CREATE TABLE Podcasts (
    ID_podcast INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_subida DATE,
    archivo VARCHAR(255),
    categoria VARCHAR(50),
    thumbnail TEXT
)";
$conn->query($sql);

// Crear tabla Seguimiento
$sql = "CREATE TABLE Seguimiento (
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    estado VARCHAR(20),
    PRIMARY KEY (id_emisor, id_receptor),
    FOREIGN KEY (id_emisor) REFERENCES Usuarios(ID_usuario),
    FOREIGN KEY (id_receptor) REFERENCES Usuarios(ID_usuario)
)";
$conn->query($sql);

// Crear tabla Mensajes
$sql = "CREATE TABLE Mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_emisor) REFERENCES Usuarios(ID_usuario),
    FOREIGN KEY (id_receptor) REFERENCES Usuarios(ID_usuario)
)";
$conn->query($sql);

// Crear tabla Notificaciones
$sql = "CREATE TABLE Notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ID_usuario INT,
    contenido TEXT,
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_usuario) REFERENCES Usuarios(ID_usuario)
)";
$conn->query($sql);

// Crear tabla Comentarios
$sql = "CREATE TABLE comentarios (
    ID_comentario INT AUTO_INCREMENT PRIMARY KEY,
    ID_usuario INT NOT NULL,
    ID_podcast INT NOT NULL,
    contenido TEXT NOT NULL,
    fecha_comentario DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ID_usuario) REFERENCES Usuarios(ID_usuario),
    FOREIGN KEY (ID_podcast) REFERENCES Podcasts(ID_podcast)
)";
$conn->query($sql);

// Crear tabla Usuarios Pendientes con correo único
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
    FOREIGN KEY (email) REFERENCES Usuarios(correo_electronico) ON DELETE CASCADE
)";
$conn->query($sql);

// Evento para eliminar tokens caducados (después de 24h)
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

// Cerrar conexión
$conn->close();
?>
