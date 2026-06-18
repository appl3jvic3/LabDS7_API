<?php
// Suppress warnings before output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// SET HEADERS FIRST (before any output)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../clases/Database.php';
require_once __DIR__ . '/../clases/Producto.php';
require_once __DIR__ . '/../clases/Auth.php';

// Obtener método HTTP y acción
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// ============================================
// DETECTAR LOGIN POR RUTA
// ============================================
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$path = parse_url($request_uri, PHP_URL_PATH) ?? '';
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
// FUNCIÓN PARA OBTENER TOKEN (WAMP compatible)
// ============================================
function getToken()
{
    $token = '';

    // Método 1: getallheaders() (Apache con mod_php)
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $auth_header = $value;
                if (strpos($auth_header, 'Bearer ') === 0) {
                    $token = substr($auth_header, 7);
                } else {
                    $token = $auth_header;
                }
                break;
            }
        }
    }

    // Método 2: $_SERVER['HTTP_AUTHORIZATION'] (WAMP en Apache)
    if (empty($token) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
        } else {
            $token = $auth_header;
        }
    }

    // Método 3: $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] (algunos Apache)
    if (empty($token) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        if (strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
        } else {
            $token = $auth_header;
        }
    }

    // Método 4: Parámetro GET (para debug/pruebas)
    if (empty($token) && isset($_GET['token'])) {
        $token = $_GET['token'];
    }

    return trim($token);
}

// ============================================
// VALIDAR TOKEN PARA EL RESTO DE ENDPOINTS
// ============================================
$token = getToken();

// Validar que hay un token
if (empty($token)) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Token no proporcionado',
        'info' => 'Usa header: Authorization: Bearer <token>'
    ]);
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
                echo json_encode(['success' => true, 'message' => 'Producto guardado']);
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
                echo json_encode(['success' => true, 'message' => 'Producto actualizado']);
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
