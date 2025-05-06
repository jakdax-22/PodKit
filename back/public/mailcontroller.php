<?php
// Incluir la clase PHPMailer
include "../vendor/class.phpmailer.php";
require_once '../credenciales/credenciales.php';

// Establecer encabezados CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Si la solicitud es POST (registro)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'register') {
    // Obtener los datos enviados por POST
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'];
    $email = $input['email'];
    $password = password_hash($input['password'], PASSWORD_DEFAULT);
    $rol = 2; // Asignar rol de usuario normal por defecto
    $token = bin2hex(random_bytes(16)); // Token único para la confirmación
    $created_at = date('Y-m-d H:i:s'); // Hora actual para verificar la expiración del token

    try {
        // Verificar si el correo ya está registrado
        $query = "SELECT * FROM usuarios WHERE correo_electronico = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "El correo electrónico ya está registrado."]);
            exit();
        }

        // Verificar si el nombre de usuario ya está registrado
        $query = "SELECT * FROM usuarios WHERE nombre_usuario = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "El nombre de usuario ya está registrado."]);
            exit();
        }

        // Guardar datos temporalmente en la tabla `pending_users`
        $query = "INSERT INTO pending_users (nombre_usuario, correo_electronico, contrasena, rol, token, created_at) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $username, $email, $password, $rol, $token, $created_at);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Configuración de PHPMailer
            $emailSender = new phpmailer();
            $emailSender->SMTPDebug = 0; // Desactivar depuración
            $emailSender->Mailer = "smtp";
            $emailSender->SMTPSecure = 'tls';
            $emailSender->Host = "smtp.gmail.com";
            $emailSender->Port = 587;
            $emailSender->SMTPAuth = true;
            $emailSender->Username = "alumnosdawes@cifpcuenca.es";
            $emailSender->Password = "oaikqunnhayotcfd";
            $emailSender->From = "enriqueiranzomartinez4@gmail.com";
            $emailSender->FromName = "Kike";
            $emailSender->Timeout = 300;

            // Configurar destinatario y contenido del correo
            $emailSender->addAddress($email);
            $emailSender->Subject = "Confirma tu cuenta";
            $emailSender->isHTML(true);

            // Plantilla HTML para el correo
            $emailSender->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; text-align: center;'>
                    <h1 style='color: #4CAF50;'>¡Bienvenido, $username!</h1>
                    <p>Gracias por registrarte. Haz clic en el botón a continuación para confirmar tu cuenta:</p>
                    <a href='http://localhost:88/mailcontroller.php?action=confirmAccount&token=$token' 
                       style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>
                        Confirmar Cuenta
                    </a>
                    <p style='margin-top: 20px;'>Si no te registraste, ignora este correo.</p>
                </div>
            ";

            // Intentar enviar el correo y capturar errores
            if (!$emailSender->send()) {
                echo json_encode([ "status" => "error", "message" => "Error al enviar el correo: " . $emailSender->ErrorInfo ]);
            } else {
                echo json_encode([ "status" => "success", "message" => "Correo de confirmación enviado. Revisa tu correo." ]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error al registrar usuario"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Ocurrió un error inesperado."]);
    }
}

// Confirmar la cuenta (cuando se hace clic en el enlace de confirmación)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'confirmAccount') {
    $token = $_GET['token'];

    try {
        // Verificar el token y la fecha de creación del token
        $query = "SELECT * FROM pending_users WHERE token = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $created_at = new DateTime($user['created_at']);
            $now = new DateTime();
            $interval = $created_at->diff($now);

            // Comprobar si el token ha expirado (10 minutos)
            if ($interval->i > 10) {
                // Eliminar de la tabla temporal
                $deleteQuery = "DELETE FROM pending_users WHERE token = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->bind_param("s", $token);
                $deleteStmt->execute();
                echo json_encode(["status" => "error", "message" => "El token ha expirado. Solicita un nuevo correo de confirmación."]);
                exit();
            }

            // Insertar el usuario en la tabla principal
            $insertQuery = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena, foto_perfil, rol) VALUES (?, ?, ?, ?, ?)";
            $fotoPerfilDefault = "default.jpg"; // Foto por defecto
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("sssii", $user['nombre_usuario'], $user['correo_electronico'], $user['contrasena'], $fotoPerfilDefault, $user['rol']);
            $insertStmt->execute();

            // Eliminar de la tabla temporal
            $deleteQuery = "DELETE FROM pending_users WHERE token = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("s", $token);
            $deleteStmt->execute();

            // Responder con una página bonita y redirigir al login
            echo "
                <html>
                    <head>
                        <meta http-equiv='refresh' content='3;url=http://localhost:3000/login' />
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                background-color: #f0f0f0;
                                color: #333;
                                text-align: center;
                                padding: 50px;
                            }
                            .container {
                                background-color: #ffffff;
                                padding: 30px;
                                border-radius: 10px;
                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                                display: inline-block;
                            }
                            h1 {
                                color: #4CAF50;
                            }
                            p {
                                font-size: 18px;
                            }
                            .btn {
                                margin-top: 20px;
                                padding: 10px 20px;
                                background-color: #4CAF50;
                                color: white;
                                text-decoration: none;
                                border-radius: 5px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h1>¡Cuenta confirmada exitosamente!</h1>
                            <p>Tu cuenta ha sido confirmada. Serás redirigido a la página de login en 3 segundos.</p>
                            <a href='http://localhost:3000/login' class='btn'>Ir al Login</a>
                        </div>
                    </body>
                </html>
            ";
        } else {
            echo json_encode(["status" => "error", "message" => "Token no válido o ya usado."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Ocurrió un error inesperado."]);
    }
}

// Recuperar contraseña (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'recoverPassword') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'];

    // Verificar si el correo existe en la base de datos
    $query = "SELECT * FROM usuarios WHERE correo_electronico = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generar un token único para el restablecimiento de contraseña
        $token = bin2hex(random_bytes(16)); // Token único
        $created_at = date('Y-m-d H:i:s'); // Hora actual para verificar la expiración del token

        // Guardar el token en la base de datos (en una tabla de tokens de recuperación)
        $query = "INSERT INTO password_reset_tokens (email, token, created_at) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $email, $token, $created_at);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Configuración de PHPMailer
            $emailSender = new phpmailer();
            $emailSender->SMTPDebug = 0; // Desactivar depuración
            $emailSender->Mailer = "smtp";
            $emailSender->SMTPSecure = 'tls';
            $emailSender->Host = "smtp.gmail.com";
            $emailSender->Port = 587;
            $emailSender->SMTPAuth = true;
            $emailSender->Username = "alumnosdawes@cifpcuenca.es";
            $emailSender->Password = "oaikqunnhayotcfd";
            $emailSender->From = "enriqueiranzomartinez4@gmail.com";
            $emailSender->FromName = "Kike";
            $emailSender->Timeout = 300;

            // Configurar destinatario y contenido del correo
            $emailSender->addAddress($email);
            $emailSender->Subject = "Recupera tu contraseña";
            $emailSender->isHTML(true);

            // Plantilla HTML para el correo de recuperación
            $emailSender->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; text-align: center;'>
                    <h1 style='color: #4CAF50;'>¿Olvidaste tu contraseña?</h1>
                    <p>Haz clic en el botón a continuación para restablecer tu contraseña:</p>
                    <a href='http://localhost:88/mailcontroller.php?action=showResetForm&token=$token' 
                       style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>
                        Restablecer Contraseña
                    </a>
                    <p style='margin-top: 20px;'>Si no solicitaste esto, ignora este correo.</p>
                </div>
            ";

            // Intentar enviar el correo y capturar errores
            if (!$emailSender->send()) {
                echo json_encode([ "status" => "error", "message" => "Error al enviar el correo: " . $emailSender->ErrorInfo ]);
            } else {
                echo json_encode([ "status" => "success", "message" => "Correo de recuperación enviado. Revisa tu correo." ]);
            }
        } else {
            echo json_encode([ "status" => "error", "message" => "Error al generar el token." ]);
        }
    } else {
        echo json_encode([ "status" => "error", "message" => "El usuario no existe"]);
    }
}
// Formulario de restablecimiento de contraseña (HTML)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'showResetForm') {
    $token = $_GET['token'];
    echo "
    <html>
    <head>
        <title>Restablecer Contraseña</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            form { display: inline-block; background: #f9f9f9; padding: 20px; border-radius: 5px; }
            input { display: block; margin: 10px auto; padding: 10px; width: 80%; }
            button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h2>Restablecer Contraseña</h2>
        <form method='POST' action='mailcontroller.php?action=resetPassword'>
            <input type='hidden' name='token' value='$token'>
            <input type='password' name='password' placeholder='Nueva Contraseña' required>
            <button type='submit'>Restablecer</button>
        </form>
    </body>
    </html>
    ";
}

// Procesar la solicitud de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'resetPassword') {
    $input = $_POST;
    $token = $input['token'];
    $newPassword = password_hash($input['password'], PASSWORD_DEFAULT);

    try {
        // Verificar si el token es válido
        $query = "SELECT email FROM password_reset_tokens WHERE token = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $email = $user['email'];

            // Actualizar la contraseña en la base de datos
            $updateQuery = "UPDATE usuarios SET contrasena = ? WHERE correo_electronico = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ss", $newPassword, $email);
            $updateStmt->execute();

            // Eliminar el token de la base de datos
            $deleteQuery = "DELETE FROM password_reset_tokens WHERE token = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("s", $token);
            $deleteStmt->execute();

            echo "
            <html>
                <head>
                    <meta http-equiv='refresh' content='3;url=http://localhost:3000/login' />
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                        .container { background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); display: inline-block; }
                        h1 { color: #4CAF50; }
                        p { font-size: 18px; }
                        .btn { margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h1>¡Contraseña restablecida!</h1>
                        <p>Tu contraseña ha sido cambiada exitosamente. Serás redirigido al login en 3 segundos.</p>
                        <a href='http://localhost:3000/login' class='btn'>Ir al Login</a>
                    </div>
                </body>
            </html>
            ";
        } else {
            echo json_encode(["status" => "error", "message" => "Token inválido o expirado."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error al actualizar la contraseña."]);
    }
}
