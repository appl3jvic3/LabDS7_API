<?php
require_once 'Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    private $db;
    private $secret = JWT_SECRET;
    private $algo = JWT_ALGORITHM;

    public function __construct()
    {
        $this->db = (new Database())->conexion;
    }

    public function login($username, $password)
    {
        $stmt = $this->db->prepare("SELECT id, username, password_hash FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $payload = [
                'iat' => time(),
                'exp' => time() + 3600,
                'user_id' => $user['id'],
                'username' => $user['username']
            ];
            try {
                $token = JWT::encode($payload, $this->secret, $this->algo);
                return ['success' => true, 'token' => $token];
            } catch (Exception $e) {
                // Error al generar el token (clave muy corta, etc.)
                return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
            }
        }
        return ['success' => false, 'message' => 'Credenciales incorrectas'];
    }

    public function validarToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algo));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}
