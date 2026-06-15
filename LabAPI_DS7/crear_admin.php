<?php
require_once 'config/config.php';
require_once 'clases/Database.php';

$db = new Database();
$conn = $db->conexion;

$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $conn->prepare("INSERT INTO usuarios (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo "✅ Usuario admin creado.<br>Usuario: admin<br>Contraseña: admin123";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
