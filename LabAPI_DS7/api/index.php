<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../clases/Database.php';
require_once __DIR__ . '/../clases/Producto.php';
require_once __DIR__ . '/../clases/Auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

// Detectar si es la ruta de login
$request_uri = $_SERVER['REQUEST_URI'];
if (strpos($request_uri, '/login') !== false) {
    if ($method === 'POST') {
        $auth = new Auth();
        $result = $auth->login($input['username'] ?? '', $input['password'] ?? '');
        if ($result['success']) {
            http_response_code(200);
            echo json_encode(['token' => $result['token']]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => $result['message']]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
    exit;
}

$headers = function_exists('getallheaders') ? getallheaders() : [];
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
// Algunos servidores (Apache, FastCGI) no rellenan getallheaders() con Authorization.
// Intentar también las variables de servidor comunes que contienen el header.
if (empty($authHeader)) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
}
// Extraer token de 'Bearer <token>' de forma insensible a mayúsculas
$token = preg_replace('/^\s*Bearer\s+/i', '', trim($authHeader));
$auth = new Auth();
$payload = $auth->validarToken($token);
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o no proporcionado']);
    exit;
}

// Instanciar producto
$producto = new Producto();

// CRUD según método
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $result = $producto->obtener($_GET['id']);
            if ($result) echo json_encode($result);
            else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
        } else {
            $buscar = $_GET['buscar'] ?? '';
            $result = $producto->listar($buscar);
            echo json_encode($result);
        }
        break;

    case 'POST':
        $errores = [];
        if (Producto::validar($input, $errores)) {
            if ($producto->guardar($input)) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Producto guardado', 'accion' => 'Guardar']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errores]);
        }
        break;

    case 'PUT':
        //parse_str(file_get_contents("php://input"), $putData);
        $id = $input['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            break;
        }
        $errores = [];
        if (Producto::validar($input, $errores)) {  // ← Valida $input correcto
            if ($producto->editar($id, $input)) {
                echo json_encode(['success' => true, 'message' => 'Producto actualizado', 'accion' => 'Modificar']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errores]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            break;
        }
        if ($producto->eliminar($id)) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no soportado']);
}
