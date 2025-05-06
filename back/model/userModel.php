<?php
include_once '../credenciales/credenciales.php';
class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    // Lógica para verificar el inicio de sesión en la base de datos
    public function login($email, $password) {
        // Consulta para obtener los datos del usuario basado en el correo electrónico
        $query = "SELECT * FROM usuarios WHERE correo_electronico = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verificar la contraseña utilizando password_verify
            if (password_verify($password, $user['contrasena'])) {
                // Si la contraseña es válida, devolver los datos del usuario
                return $user;
            } else {
                // Si la contraseña no es válida, devolver false
                return false;
            }
        } else {
            // Si no se encuentra el usuario, devolver false
            return false;
        }
    }
    
    

    public function register($username, $email, $password) {
        $defaultRole = 2;
    
        $query = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contraseña, rol_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssi", $username, $email, $password, $defaultRole);
        
        if ($stmt->execute()) {
            // Obtiene el ID del usuario recién registrado
            $userId = $this->db->insert_id;
    
            // Inserta la notificación de bienvenida
            $notificationQuery = "INSERT INTO notificaciones (ID_usuario, contenido, fecha_emision) VALUES (?, ?, NOW())";
            $notificationStmt = $this->db->prepare($notificationQuery);
            $contenido = "¡Bienvenido a Susurros Vegetales!";
            $notificationStmt->bind_param("is", $userId, $contenido);
            
            if ($notificationStmt->execute()) {
                return true;
            } else {
                error_log("Error al insertar la notificación: " . $notificationStmt->error);
                return false;
            }
        } else {
            error_log("Error al registrar usuario: " . $stmt->error);
            return false;
        }
    }
    
    
    public function getAllUsers($selfId) {
        $query = "SELECT ID_usuario, nombre_usuario, foto_perfil 
                  FROM usuarios 
                  WHERE ID_usuario != ? 
                  AND ID_usuario NOT IN (
                      SELECT id_emisor 
                      FROM Seguimiento 
                      WHERE id_receptor = ? 
                      AND estado = 'Aceptada'
                      UNION
                      SELECT id_receptor 
                      FROM Seguimiento 
                      WHERE id_emisor = ? 
                      AND estado = 'Aceptada'
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $selfId, $selfId, $selfId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    
        echo json_encode($users);
    }
    public function fetchAllUsers($selfId) {
        $query = "SELECT *
                  FROM usuarios 
                  WHERE ID_usuario != ? 
                  ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $selfId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    
        echo json_encode($users);
    }
    public function makeAdmin($userId) {    
        // Preparar la consulta SQL para actualizar el usuario
        $query = "UPDATE usuarios SET rol = 1 WHERE ID_usuario=?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $userId);
    
        // Ejecutar la consulta preparada
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    

    public function getUserData($userId){
        $query = "SELECT ID_usuario, nombre_usuario, correo_electronico, foto_perfil, rol FROM usuarios WHERE ID_usuario = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user;
    }

    public function updateUserData($userId,$name,$email,$avatarPath) {    
        // Preparar la consulta SQL para actualizar el usuario
        $query = "UPDATE usuarios SET nombre_usuario=?, correo_electronico=?, foto_perfil=? WHERE ID_usuario=?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssi", $name,$email,$avatarPath, $userId);
    
        // Ejecutar la consulta preparada
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
