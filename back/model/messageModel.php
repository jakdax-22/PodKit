<?php
include_once '../credenciales/credenciales.php';

class MessageModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    public function sendMessage($selfId, $receptorId, $message) {
        $query = "INSERT INTO mensajes (id_emisor, id_receptor, mensaje) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iis", $selfId, $receptorId, $message);
        if ($stmt->execute()) {
            return["success" => true, "message" => "Mensaje enviado"];
        } else {
            return["success" => false, "message" => "Error al enviar el mensaje"];
        }
    }
    public function getMessages($selfId, $receptorId) {
        $query = "SELECT * FROM mensajes WHERE (id_emisor = ? AND id_receptor = ?) OR (id_emisor = ? AND id_receptor = ?) ORDER BY fecha ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iiii", $selfId, $receptorId, $receptorId, $selfId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        return $messages;
    }
    
}