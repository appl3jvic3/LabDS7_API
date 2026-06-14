<?php
require_once __DIR__ . '/Database.php';

class Producto {
    private $db;
    private $tabla = "productos";

    public function __construct() {
        $this->db = (new Database())->conexion;
    }

    // Guardar producto
    public function guardar($datos) {
        $sql = "INSERT INTO productos (codigo, producto, precio, cantidad) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$datos['codigo'], $datos['producto'], $datos['precio'], $datos['cantidad']]);
    }

    // Actualizar producto
    public function editar($id, $datos) {
        $sql = "UPDATE productos SET codigo=?, producto=?, precio=?, cantidad=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$datos['codigo'], $datos['producto'], $datos['precio'], $datos['cantidad'], $id]);
    }

    // Obtener un producto por ID
    public function obtener($id) {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Listar todos los productos (para búsqueda y listado)
    public function listar($busqueda = '') {
        if ($busqueda) {
            $sql = "SELECT * FROM productos WHERE codigo LIKE ? OR producto LIKE ?";
            $param = "%$busqueda%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$param, $param]);
        } else {
            $sql = "SELECT * FROM productos";
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Eliminar producto
    public function eliminar($id) {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Validar campos (puede usarse antes de guardar/editar)
    public static function validar($datos, &$errores) {
        if (empty($datos['codigo'])) $errores['codigo'] = "El código es obligatorio";
        if (empty($datos['producto'])) $errores['producto'] = "El producto es obligatorio";
        if (!is_numeric($datos['precio']) || $datos['precio'] <= 0) $errores['precio'] = "Precio inválido";
        if (!is_numeric($datos['cantidad']) || $datos['cantidad'] < 0) $errores['cantidad'] = "Cantidad inválida";
        return count($errores) === 0;
    }
}
?>