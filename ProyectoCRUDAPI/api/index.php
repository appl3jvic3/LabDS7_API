<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../clases/Database.php';
require_once __DIR__ . '/../clases/Producto.php';
require_once __DIR__ . '/../clases/Auth.php';

// Obtener método HTTP y acción
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

// ============================================
// DETECTAR LOGIN POR RUTA (no por GET)
// ============================================
$request_uri = $_SERVER['REQUEST_URI'];
// Eliminar parámetros de la URL si los hay (ej: ?buscar=...)
$path = parse_url($request_uri, PHP_URL_PATH);
$isLogin = (strpos($path, '/login') !== false);

if ($isLogin) {
    if ($method === 'POST') {
        $auth = new Auth();
        $result = $auth->login($input['username'] ?? '', $input['password'] ?? '');
        if ($result['success']) {
            http_response_code(200);
            echo json_encode(['token' => $result['token']]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
    exit;
}

// ============================================
// VALIDAR TOKEN PARA EL RESTO DE ENDPOINTS
// ============================================
// Obtener token del header Authorization
$token = '';
$headers = getallheaders();

// Verificar diferentes formas de enviar el token
if (isset($headers['Authorization'])) {
    $auth_header = $headers['Authorization'];
    if (strpos($auth_header, 'Bearer ') === 0) {
        $token = substr($auth_header, 7); // Remover "Bearer "
    } else {
        $token = $auth_header;
    }
}

// Si no viene en Authorization, intentar desde GET (para pruebas)
if (empty($token) && isset($_GET['token'])) {
    $token = $_GET['token'];
}

// Validar que hay un token
if (empty($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Token no proporcionado. Use header: Authorization: Bearer <token>']);
    exit;
}

// Validar el token
$auth = new Auth();
$payload = $auth->validarToken($token);
if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
    exit;
}

// Instanciar controlador de productos
$producto = new Producto();

// Enrutamiento según método (GET, POST, PUT, DELETE)
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $result = $producto->obtener($_GET['id']);
            if ($result) {
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
        } else {
            $busqueda = $_GET['buscar'] ?? '';
            $result = $producto->listar($busqueda);
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
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            break;
        }
        $errores = [];
        if (Producto::validar($input, $errores)) {
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
