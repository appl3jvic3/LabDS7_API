<?php
require_once 'clases/Database.php';
$db = new Database();
$conn = $db->conexion;

$username = 'Jeremy';
$password = 'pasaclave123';   // cámbiala después
$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $conn->prepare("INSERT INTO usuarios (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo "Usuario admin creado exitosamente.<br>";
    echo "Username: $username<br>Contraseña: $password";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>