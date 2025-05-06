<?php
include_once '../credenciales/credenciales.php';

class PodcastModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

// Función para insertar un nuevo podcast en la base de datos y notificar a los usuarios
public function insertPodcast($title, $description, $category, $file, $thumbnail) {
    $currentDate = date("Y-m-d"); // Obtener la fecha actual

    // Insertar el podcast
    $query = "INSERT INTO podcasts (titulo, descripcion, fecha_subida, archivo, categoria, thumbnail) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("ssssss", $title, $description, $currentDate, $file, $category, $thumbnail);

    if ($stmt->execute()) {
        // Obtener el ID del podcast recién insertado
        $podcastID = $stmt->insert_id;

        // Llamar a la función para notificar a los usuarios
        $this->notifyUsers($title, $podcastID);

        return true;
    } else {
        return false;
    }
}

// Función para insertar notificaciones a todos los usuarios
private function notifyUsers($podcastTitle, $podcastID) {
    $currentDate = date("Y-m-d H:i:s");

    // Obtener todos los usuarios
    $queryUsers = "SELECT ID_usuario FROM usuarios";
    $result = $this->db->query($queryUsers);

    if ($result && $result->num_rows > 0) {
        $stmt = $this->db->prepare("INSERT INTO notificaciones (ID_usuario, contenido, fecha_emision) VALUES (?, ?, ?)");
        
        while ($row = $result->fetch_assoc()) {
            $userID = $row['ID_usuario'];
            $content = "Se ha subido un nuevo podcast: '$podcastTitle'.";

            $stmt->bind_param("iss", $userID, $content, $currentDate);
            $stmt->execute();
        }
        $stmt->close();
    }
}

    public function updatePodcast($id, $title, $description, $category, $file, $thumbnail) {    
        // Preparar la consulta SQL para actualizar el podcast
        $query = "UPDATE podcasts SET titulo=?, descripcion=?, categoria=?, archivo=?, thumbnail=? WHERE ID_podcast=?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssi", $title, $description, $category, $file, $thumbnail, $id);
    
        // Ejecutar la consulta preparada
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Función para sacar todos los podcasts
    public function showPodcasts() {
        // Consulta
        $query = "SELECT * FROM podcasts";
        $stmt = $this->db->prepare($query);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $podcast = $result->fetch_all(MYSQLI_ASSOC);
            return $podcast;
        } else {
            return false;
        }
    }
    // Función para sacar información de un podcast
    public function getPodcast($id) {
        // Consulta
        $query = "SELECT * FROM podcasts WHERE ID_podcast = ?";
        $stmt = $this->db->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("i", $id);
            // Ejecutar la consulta preparada
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $podcast = $result->fetch_assoc();
                return $podcast;

            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function deletePodcast($id) {
        $idDelete = intval($id);
    
        // Iniciar una transacción para asegurar consistencia
        $this->db->begin_transaction();
    
        try {
            // Eliminar primero los comentarios asociados al podcast
            $queryComments = "DELETE FROM comentarios WHERE ID_podcast = ?";
            $stmtComments = $this->db->prepare($queryComments);
    
            if ($stmtComments) {
                $stmtComments->bind_param("i", $idDelete);
                if (!$stmtComments->execute()) {
                    throw new Exception("Error al eliminar comentarios: " . $stmtComments->error);
                }
            } else {
                throw new Exception("Error en la preparación de la consulta de comentarios");
            }
    
            // Ahora eliminar el podcast
            $queryPodcast = "DELETE FROM podcasts WHERE ID_podcast = ?";
            $stmtPodcast = $this->db->prepare($queryPodcast);
    
            if ($stmtPodcast) {
                $stmtPodcast->bind_param("i", $idDelete);
                if (!$stmtPodcast->execute()) {
                    throw new Exception("Error al eliminar el podcast: " . $stmtPodcast->error);
                }
            } else {
                throw new Exception("Error en la preparación de la consulta del podcast");
            }
    
            // Si todo fue bien, confirmar la transacción
            $this->db->commit();
            return true;
    
        } catch (Exception $e) {
            // Si hay algún error, revertir la transacción
            $this->db->rollback();
            error_log($e->getMessage()); // Registrar el error
            return false;
        }
    }
    
    
    
}
