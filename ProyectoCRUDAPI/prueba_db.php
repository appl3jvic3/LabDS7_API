<?php
require_once 'clases/Database.php';
$db = new Database();
$conn = $db->conexion;
$stmt = $conn->query("SELECT COUNT(*) FROM usuarios");
echo "Conexión exitosa. Usuarios en tabla: " . $stmt->fetchColumn();
?>