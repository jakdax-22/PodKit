<?php
include_once '../credenciales/credenciales.php';

class FriendshipModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    public function insertFriendship($selfId, $userId) {
        // Insertar la solicitud de amistad en la tabla Seguimiento
        $query = "INSERT INTO seguimiento (id_emisor, id_receptor, estado) VALUES (?, ?, 'Pendiente')";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $selfId, $userId);

        if ($stmt->execute()){
            return true;
        }
        else{
            return false;
        }
    }
    public function getFriendRequests($selfId) {        
        $query = "SELECT u.ID_usuario, u.nombre_usuario, u.foto_perfil 
                  FROM seguimiento s
                  JOIN usuarios u ON s.id_emisor = u.ID_usuario
                  WHERE s.id_receptor = ? AND s.estado = 'Pendiente'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $selfId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $friendRequests = [];
        while ($row = $result->fetch_assoc()) {
            $friendRequests[] = $row;
        }
    
        return $friendRequests;
    }
    public function acceptFriendRequest($selfId,$requestId){
        $query = "UPDATE seguimiento SET estado = 'Aceptada' WHERE id_emisor = ? AND id_receptor = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $requestId, $selfId);
        if ($stmt->execute()) {
            return(["success" => true, "message" => "Solicitud de amistad aceptada."]);
        } else {
            return(["success" => false, "message" => "Error al aceptar la solicitud de amistad."]);
        }
    }
    public function declineFriendRequest($selfId,$requestId){
        $query = "DELETE FROM seguimiento WHERE id_emisor = ? AND id_receptor = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $requestId, $selfId);
        if ($stmt->execute()) {
            return(["success" => true, "message" => "Solicitud de amistad rechazada."]);
        } else {
            return(["success" => false, "message" => "Error al rechazar la solicitud de amistad."]);
        }
    }
    public function getFriends($selfId){
        $query = "
            SELECT DISTINCT 
                u.ID_usuario, 
                u.nombre_usuario, 
                u.foto_perfil 
            FROM 
                usuarios u 
            JOIN 
                seguimiento s 
            ON 
                (u.ID_usuario = s.id_emisor OR u.ID_usuario = s.id_receptor)
            WHERE 
                (s.id_emisor = ? OR s.id_receptor = ?) 
                AND s.estado = 'Aceptada' 
                AND u.ID_usuario != ?
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $selfId, $selfId, $selfId);
        $stmt->execute();
        $result = $stmt->get_result();

        $friends = [];
        while ($row = $result->fetch_assoc()) {
            $friends[] = $row;
        }

        return $friends;
    }
}