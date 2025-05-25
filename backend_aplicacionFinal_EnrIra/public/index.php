<?php
require_once __DIR__ . '/../controller/controller.php';
require_once __DIR__ . '/../vendor/autoload.php';
include_once '../credenciales/credenciales.php';

session_start();

// Configurar cabeceras CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejo de solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Instanciar el controlador
$controller = new Controller($conn);

// Obtener el `action` de la URL
$action = $_GET['action'] ?? null;
$requestMethod = $_SERVER['REQUEST_METHOD'];

if (!$action) {
    echo json_encode(["error" => "No se especificó una acción"]);
    exit;
}

// Rutas y controladores
$routes = [
    'GET' => [
        'login' => 'login',
        'showPodcasts' => 'showPodcasts',
        'getAllUsers' => 'getAllUsers',
        'getUserData' => 'getUserData',
        'getFriendRequests' => 'getFriendRequests',
        'getMessages' => 'getMessages',
        'getFriends' => 'getFriends',
        'getPodcast' => 'getPodcast',
        'getComments' => 'getComments',
        'fetchAllUsers' => 'fetchAllUsers',
        'readNotifications' => 'readNotifications'
    ],
    'POST' => [
        'uploadPodcast' => 'insertPodcast',
        'editPodcast' => 'updatePodcast',
        'deletePodcast' => 'deletePodcast',
        'updateUserData' => 'updateUserData',
        'sendFriendRequest' => 'sendFriendRequest',
        'acceptFriendRequest' => 'acceptFriendRequest',
        'declineFriendRequest' => 'declineFriendRequest',
        'sendMessage' => 'sendMessage',
        'addComment' => 'addComment',
        'makeAdmin' => 'makeAdmin',
        'deleteNotification' => 'deleteNotification'
    ]
];

// Ejecutar el método del controlador si existe
if (isset($routes[$requestMethod][$action])) {
    $method = $routes[$requestMethod][$action];
    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        echo json_encode(["error" => "Método no definido en el controlador"]);
    }
} else {
    echo json_encode(["error" => "Acción no válida"]);
}

?>
