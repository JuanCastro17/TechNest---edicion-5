<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Forzamos el apagado de advertencias HTML para evitar que ensucien el JSON de JavaScript
error_reporting(0);
ini_set('display_errors', 0);

// CORRECCIÓN: Dos pasos atrás para salir de public/api/ hacia la raíz de TechNest
include(__DIR__ . '/../../config/conexion.php');

// TRUCO DE RESPALDO: Si la variable de conexión no cargó por la ruta, reconectamos directo a tu puerto 3307
if (!isset($conexion) || !$conexion) {
    $conexion = @mysqli_connect("127.0.0.1", "root", "", "technest", 3307);
}

if (!$conexion) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Fallo en la comunicación con la base de datos."]);
    exit();
}

mysqli_set_charset($conexion, "utf8mb4");

$id_usuario = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 0;
$id_vendedor = isset($_SESSION['id_vendedor']) ? intval($_SESSION['id_vendedor']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $chat_con = isset($_GET['chat_con']) ? intval($_GET['chat_con']) : 0;
    $u_id = ($id_vendedor > 0) ? $chat_con : $id_usuario;

    if ($u_id > 0) {
        $query = "SELECT * FROM chat_soporte WHERE id_usuario = $u_id ORDER BY id_mensaje ASC";
        $res = mysqli_query($conexion, $query);
        $mensajes = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $mensajes[] = $row;
            }
        }
        echo json_encode($mensajes, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $msg_texto = isset($input['mensaje']) ? mysqli_real_escape_string($conexion, trim($input['mensaje'])) : '';
    $chat_con = isset($input['chat_con']) ? intval($input['chat_con']) : 0;
    
    if (empty($msg_texto)) {
        echo json_encode(["status" => "error", "message" => "Mensaje vacío"]);
        exit();
    }

    if ($id_vendedor > 0) {
        $query = "INSERT INTO chat_soporte (id_usuario, id_vendedor, remitente, mensaje, estado_chat) VALUES ($chat_con, $id_vendedor, 'vendedor', '$msg_texto', 'activo')";
    } else {
        $query = "INSERT INTO chat_soporte (id_usuario, id_vendedor, remitente, mensaje, estado_chat) VALUES ($id_usuario, 1, 'cliente', '$msg_texto', 'activo')";
    }

    if (mysqli_query($conexion, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
    }
    exit();
}
?>
