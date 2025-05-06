<?php
// El encargado de conectar con el Front, la estructura es la propia del uso del MVC , esta parte es el controlador, por tanto
// las funciones van a encontrarse en el modelo correspondiente de cada clase, este componente solo se encarga de importarlas, ejecutarlas y devolver respuestas en formato JSON

require_once '../model/userModel.php';
require_once '../model/podcastModel.php';
require_once '../model/friendshipModel.php';
require_once '../model/messageModel.php';
require_once '../model/commentModel.php';
require_once '../model/notificationModel.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class Controller {
    private $db;
    private $userModel;
    private $friendshipModel;
    private $podcastModel;
    private $messageModel;
    private $commentModel;
    private $notificationModel;
    private $secret_key = "Agorazein2011"; // Clave secreta para JWT


    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new UserModel($db);
        $this->podcastModel = new PodcastModel($db);
        $this->friendshipModel = new FriendshipModel($db);
        $this->messageModel = new MessageModel($db);
        $this->commentModel = new CommentModel($db);
        $this->notificationModel = new NotificationModel($db);
    }

    // Método para generar JWT
    private function generateJWT($user) {
        $payload = [
            "id" => $user["ID_usuario"],
            "username" => $user["nombre_usuario"],
            "role" => $user["rol"],
            "exp" => time() + 3600
        ];

        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    // Método para verificar JWT
    private function authorize() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }

        try {
            $token = str_replace("Bearer ", "", $headers['Authorization']);
            return JWT::decode($token, new Key($this->secret_key, 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Token inválido"]);
            exit;
        }
    }

    // LOGIN con JWT
    public function login() {
        $username = $_GET["email"];
        $password = $_GET["password"];

        $userData = $this->userModel->login($username, $password);

        if ($userData) {
            $token = $this->generateJWT($userData);

            $response = [
                "username" => $userData["nombre_usuario"],
                "role" => $userData["rol"],
                "id" => $userData["ID_usuario"],
                "avatar" => $userData["foto_perfil"],
                "token" => $token
            ];

            echo json_encode($response);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Credenciales inválidas"]);
        }
    }
    public function insertPodcast() {
    $user = $this->authorize();

    // Validar campos obligatorios
    if (empty($_POST["title"]) || empty($_POST["description"]) || empty($_POST["category"])) {
        $this->sendErrorResponse("Datos incompletos. Asegúrate de proporcionar título, descripción y categoría.");
        return;
    }

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $localFile = $_POST["youtubeLink"] ?? null;
    $thumbnailPath = null;
    $videoPath = null;

    // Validar thumbnail
    if (!isset($_FILES["thumbnailFile"]) || $_FILES["thumbnailFile"]["error"] !== UPLOAD_ERR_OK) {
        $this->sendErrorResponse("Es obligatorio subir una imagen de thumbnail.");
        return;
    }

    // Validar y procesar thumbnail
    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES["thumbnailFile"]["type"], $allowedImageTypes)) {
        $this->sendErrorResponse("El archivo de thumbnail debe ser una imagen JPG, PNG o GIF.");
        return;
    }

    $thumbnailFileName = time() . '_' . $_FILES["thumbnailFile"]["name"];
    $thumbnailDirectory = "../public/assets/thumbnail/";
    $uploadedThumbnail = $thumbnailDirectory . $thumbnailFileName;

    if (!move_uploaded_file($_FILES["thumbnailFile"]["tmp_name"], $uploadedThumbnail)) {
        $this->sendErrorResponse("Error al subir la imagen.");
        return;
    }
    $thumbnailPath = substr($uploadedThumbnail, 10);

    // Validar contenido según opción seleccionada
    if ($localFile) {
        if (!filter_var($localFile, FILTER_VALIDATE_URL) || !preg_match("/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/", $localFile)) {
            $this->sendErrorResponse("El enlace de YouTube no es válido.");
            return;
        }
    } else if (isset($_FILES["localFile"]) && $_FILES["localFile"]["error"] === UPLOAD_ERR_OK) {
        $allowedVideoTypes = ['video/mp4', 'video/mkv', 'video/avi'];
        if (!in_array($_FILES["localFile"]["type"], $allowedVideoTypes)) {
            $this->sendErrorResponse("El archivo de video debe estar en formato MP4, MKV o AVI.");
            return;
        }

        $videoFileName = time() . '_' . $_FILES["localFile"]["name"];
        $videoDirectory = "../public/assets/podcasts/";
        $uploadedVideo = $videoDirectory . $videoFileName;

        if (!move_uploaded_file($_FILES["localFile"]["tmp_name"], $uploadedVideo)) {
            $this->sendErrorResponse("Error al subir el archivo de video.");
            return;
        }
        $videoPath = substr($uploadedVideo, 10);
        $localFile = $videoPath;
    } else {
        $this->sendErrorResponse("Debes proporcionar un enlace de YouTube o subir un archivo de video.");
        return;
    }

    // Insertar en la base de datos
    if ($this->podcastModel->insertPodcast($title, $description, $category, $localFile, $thumbnailPath)) {
        echo json_encode(["success" => true, "message" => "Podcast insertado correctamente."]);
        http_response_code(200);
    } else {
        $this->sendErrorResponse("Error al insertar el podcast.");
    }
}

// Método auxiliar para enviar respuestas de error
private function sendErrorResponse($message) {
    echo json_encode(["success" => false, "message" => $message]);
    http_response_code(400);
}
    public function updatePodcast() {
        $user = $this->authorize();
        // Obtener los datos del formulario
        $id = $_POST["id"];
        $title = $_POST["title"];
        $description = $_POST["description"];
        $category = $_POST["category"];
        //Si una no está definida coge la otra
        $localFile = isset($_FILES["localFile"]) ? $_FILES["localFile"]["name"] : $_POST["youtubeLink"];
        
        $thumbnailFileName = $_FILES["thumbnailFile"]["name"];
        $timestamp = time();
        $newFileName = $timestamp . '_' . $thumbnailFileName;

        // Directorio de destino para guardar la imagen
        $uploadDirectory = "../public/assets/thumbnail/";
        $uploadedFile = $uploadDirectory . $newFileName;

        // Mover la imagen cargada al directorio de destino
        if (move_uploaded_file($_FILES["thumbnailFile"]["tmp_name"], $uploadedFile)) {
            // Insertar la ruta de la imagen del thumbnail en la base de datos
            $thumbnailPath = substr($uploadedFile,10);
        } else {
            // Error al mover la imagen
            $thumbnailPath = null;
        }
        // Insertar el podcast en la base de datos
        if ($this->podcastModel->updatePodcast($id,$title, $description, $category, $localFile,$thumbnailPath)) {
            // El podcast se ha insertado correctamente
            $response = array("success" => true, "message" => "Podcast editado correctamente.");
            http_response_code(200);
        } else {
            // Error al insertar el podcast
            $response = array("success" => false, "message" => "Error al editar el podcast.");
            http_response_code(500);
        }
        // Devolver la respuesta en formato JSON
        echo json_encode($response);
    }
    public function showPodcasts(){
        echo json_encode($this->podcastModel->showPodcasts(), JSON_INVALID_UTF8_IGNORE);
    }
    public function deletePodcast(){
        $user = $this->authorize();
        $id = $_POST["id"];
        echo json_encode($this->podcastModel->deletePodcast($id));
    }
    public function getPodcast(){
        $podcastId = $_GET["idpodcast"];
        echo json_encode($this->podcastModel->getPodcast($podcastId),JSON_INVALID_UTF8_IGNORE);
    }
    public function getComments() {
        $podcastId = $_GET["podcastId"];
        $page = $_GET['page'];
        $pageSize = $_GET['pageSize'];
        echo json_encode($this->commentModel->getComments($podcastId, $page, $pageSize));
        
    }
    
    public function register() {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "register") {
            // Obtener los datos del formulario
            $username = $_POST["username"];
            $email = $_POST["email"];
            $password = $_POST["password"];

            // Llamar al método de registro del modelo
            $this->userModel->register($username, $email, $password);
        }
    }
    public function makeAdmin() {
        $user = $this->authorize();
        $userId = $_POST['userId'];
        echo $userId;
        $this->userModel->makeAdmin($userId);
    }
    public function getAllUsers(){
        $user = $this->authorize();
        // Obtener el ID del usuario que hace la petición
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
        $this->userModel->getAllUsers($userId);
    }
    public function fetchAllUsers(){
        $user = $this->authorize();
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
        $this->userModel->fetchAllUsers($userId);
    }
    public function readNotifications(){
        $user = $this->authorize();
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
        echo json_encode($this->notificationModel->getNotificationsByUserId($userId));
    }
    function getUserData() {
        $user = $this->authorize();
    
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
        
        $user = $this->userModel->getUserData($userId);
        
        echo json_encode($user);
    }
    
    function updateUserData() {
        $user = $this->authorize();

        $userId = intval($_POST['id']);
        $name = $_POST['name'];
        $email = $_POST['email'];
    
        // Verificar si el nombre de usuario o correo electrónico ya existe para otro usuario
        $query = "SELECT ID_usuario FROM usuarios WHERE (nombre_usuario = ? OR correo_electronico = ?) AND ID_usuario != ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $name, $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            echo json_encode(["error" => "El usuario o el correo ya existen"]);
            return;
        }
    
        // Manejar subida de avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            $thumbnailFileName = $_FILES["avatar"]["name"];
            $timestamp = time();
            $newFileName = $timestamp . '_' . $thumbnailFileName;
    
            // Directorio de destino para guardar la imagen
            $uploadDirectory = "../public/assets/avatars/";
            $uploadedFile = $uploadDirectory . $newFileName;
    
            // Mover la imagen cargada al directorio de destino
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $uploadedFile)) {
                // Actualizar la ruta del avatar
                $avatarPath = substr($uploadedFile,10);
            } else {
                echo json_encode(["error" => "Error subiendo el avatar"]);
                return;
            }
        }
        // Insertar el podcast en la base de datos
        if ($this->userModel->updateUserData($userId,$name,$email,$avatarPath)) 
        {
            // El usuario se ha editado correctamente
            $response = array("success" => true, "message" => "Usuario editado correctamente.");
            http_response_code(200);
        } else {
            // Error al editar el usuario
            $response = array("success" => false, "message" => "Error al editar el usuario.");
            http_response_code(500);
        }
        // Devolver la respuesta en formato JSON
        echo json_encode($response);
    }
    public function sendFriendRequest() {
        $user = $this->authorize();
        $selfId = intval($_POST['id_emisor']);
        $userId = intval($_POST['id_receptor']);
        // Verificar si ya existe una solicitud pendiente entre los usuarios
        $query = "SELECT * FROM Seguimiento WHERE (id_emisor = ? AND id_receptor = ?) OR (id_emisor = ? AND id_receptor = ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iiii", $selfId, $userId, $userId, $selfId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Ya existe una solicitud pendiente entre los usuarios
            echo json_encode(["error" => "Ya existe una solicitud pendiente entre los usuarios"]);
            return;
        }
        if ($this->friendshipModel->insertFriendship($selfId,$userId)) {
            // Solicitud de amistad enviada correctamente
            echo json_encode(["success" => true, "message" => "Solicitud de amistad enviada correctamente"]);
        } else {
            // Error al enviar la solicitud de amistad
            echo json_encode(["error" => "Error al enviar la solicitud de amistad"]);
        }
    }
    public function getFriendRequests() {
        $user = $this->authorize();
        $selfId = intval($_GET['selfId']);
    
        echo json_encode($this->friendshipModel->getFriendRequests($selfId));
    }

    public function acceptFriendRequest() {
        $user = $this->authorize();
        $requestId = $_POST['requestId'];
        $selfId = $_POST['selfId'];
        
        echo json_encode($this->friendshipModel->acceptFriendRequest($selfId,$requestId));
    }
    
    public function declineFriendRequest() {
        $user = $this->authorize();
        $requestId = $_POST['requestId'];
        $selfId = $_POST['selfId'];
        
        echo json_encode($this->friendshipModel->declineFriendRequest($selfId,$requestId));
    }
    public function getFriends() {
        $user = $this->authorize();
        $selfId = intval($_GET['id']);
        
        echo json_encode($this->friendshipModel->getFriends($selfId));
    }

    public function sendMessage() {
        $user = $this->authorize();
        $selfId = intval($_POST['id_emisor']);
        $receptorId = intval($_POST['id_receptor']);
        $message = $_POST['mensaje'];

        echo json_encode($this->messageModel->sendMessage($selfId,$receptorId,$message));

    }
    public function addComment() {
        $user = $this->authorize();
    
        $podcastId = intval($_POST['podcastId'] ?? 0);
        $userId = $_POST['userId'] ?? null;
        $content = $_POST['content'] ?? '';
    
        // Validar que los datos sean correctos
        if (!$podcastId || !$userId || empty($content)) {
            echo json_encode(["success" => false, "message" => "Datos incompletos"]);
            exit();
        }
    
        // Insertar comentario
        echo json_encode($this->commentModel->addComment($podcastId, $userId, $content));
    }
    

    public function getMessages() {
        $user = $this->authorize();
        $id_emisor = intval($_GET['id_emisor']);
        $id_receptor = intval($_GET['id_receptor']);
        echo json_encode($this->messageModel->getMessages($id_emisor,$id_receptor));
    }

    public function deleteNotification() {
        $user = $this->authorize();
        $selfId = intval($_POST['id']);
        $notificationId = intval($_POST['notificationId']);
        echo json_encode($this->notificationModel->deleteNotifications($selfId,$notificationId));
    }
    
}
