<?php
include_once '../credenciales/credenciales.php';

class CommentModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getComments($podcastId, $page, $pageSize) {
        // Calcula el desplazamiento para la paginación
        $offset = ($page - 1) * $pageSize;
    
        // Prepara la consulta SQL con paginación
        $stmt = $this->db->prepare("
            SELECT comentarios.ID_comentario AS id, 
                   comentarios.contenido AS content, 
                   comentarios.fecha_comentario AS date,
                   usuarios.nombre_usuario AS userName, 
                   usuarios.foto_perfil AS userPhoto,
                   usuarios.rol = 1 AS isAdmin
            FROM comentarios
            INNER JOIN usuarios ON comentarios.ID_usuario = usuarios.ID_usuario
            WHERE comentarios.ID_podcast = ?
            ORDER BY comentarios.fecha_comentario DESC
            LIMIT ? OFFSET ?
        ");
    
        if (!$stmt) {
            return false;
        }
    
        // Asocia los parámetros a la consulta
        $stmt->bind_param("iii", $podcastId, $pageSize, $offset);
    
        // Ejecuta la consulta
        if (!$stmt->execute()) {
            return false;
        }
    
        // Obtiene los resultados
        $result = $stmt->get_result();
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
    
        return $comments;
    }
    
    
        public function addComment($podcastId, $userId, $content){
            $query = "INSERT INTO comentarios (contenido, ID_podcast, ID_usuario) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sii", $content, $podcastId, $userId);
            if ($stmt->execute()) {
                return["success" => true, "message" => "Comentario enviado"];
            } else {
                return["success" => false, "message" => $this->db->errno];
            }
        }
            
}