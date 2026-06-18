<?php
require_once __DIR__ . '/clases/Database.php';

echo "<h2>Prueba de Conexión y Credenciales</h2>";

// 1. Probar la conexión a la base de datos
try {
    $db = new Database();
    $conn = $db->conexion;
    echo "<p style='color: green;'>✅ Conexión a la base de datos: <b>EXITOSA</b>.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión a la BD: " . $e->getMessage() . "</p>";
    exit; // Detener aquí si no hay base de datos
}

// 2. Probar el usuario y la contraseña (Cámbialos si usaste otros)
$username = 'Jeremy';
$password = 'pasaclave123';

echo "<hr><p>Probando credenciales para el usuario: <b>$username</b></p>";

try {
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<p>✅ Usuario encontrado en la base de datos.</p>";
        
        // Verificar la contraseña contra el hash
        if (password_verify($password, $user['password_hash'])) {
            echo "<p style='color: green; font-size: 1.2em;'>✅ <b>¡La contraseña es correcta!</b> El login interno funciona perfectamente.</p>";
        } else {
            echo "<p style='color: red;'>❌ La contraseña es incorrecta.</p>";
            echo "<p><small>Hash guardado: " . $user['password_hash'] . "</small></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ El usuario '$username' no existe en la tabla.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error al consultar la tabla usuarios: " . $e->getMessage() . "</p>";
}
?>