<?php
include_once '../credenciales/credenciales.php';

class NotificationModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getNotificationsByUserId($id) {
        $query = "SELECT * FROM notificaciones WHERE ID_usuario = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        return $notifications;
    }

    public function deleteNotifications($userId, $notificationId) {
        $query = "DELETE FROM notificaciones WHERE ID_usuario = ? AND id = ?";
        $stmt = $this->db->prepare($query);
    
        if ($stmt) {
            $stmt->bind_param("ii", $userId, $notificationId);
            if ($stmt->execute()) {
                return ["success" => true, "message" => "Notificación eliminada correctamente"];
            } else {
                return ["success" => false, "error" => "Error al eliminar la notificación"];
            }
        } else {
            return ["success" => false, "error" => "Error en la preparación de la consulta"];
        }
    }
    
}

