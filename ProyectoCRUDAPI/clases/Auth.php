<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    private $db;
    private $secret;

    public function __construct()
    {
        $this->db = (new Database())->conexion;
        // Asignar el secret en el constructor, no en la propiedad
        $this->secret = JWT_SECRET;
    }

    // Verificar credenciales y generar token
    public function login($username, $password)
    {
        $stmt = $this->db->prepare("SELECT id, username, password_hash FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $payload = [
                'iat' => time(),
                'exp' => time() + 3600, // 1 hora
                'user_id' => $user['id'],
                'username' => $user['username']
            ];
            $token = JWT::encode($payload, $this->secret, 'HS256');
            return ['success' => true, 'token' => $token];
        }
        return ['success' => false, 'message' => 'Credenciales incorrectas'];
    }

    // Validar token (devuelve payload o false)
    public function validarToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}
