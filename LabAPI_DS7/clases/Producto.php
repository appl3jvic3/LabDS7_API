<?php
require_once 'Database.php';

class Producto
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->conexion;
    }

    // Validación estática (para reutilizar)
    public static function validar($datos, &$errores)
    {
        if (empty($datos['codigo'])) $errores['codigo'] = "Código requerido";
        if (empty($datos['producto'])) $errores['producto'] = "Producto requerido";
        if (!is_numeric($datos['precio']) || $datos['precio'] <= 0) $errores['precio'] = "Precio inválido (>0)";
        if (!is_numeric($datos['cantidad']) || $datos['cantidad'] <= 0) $errores['cantidad'] = "Cantidad no negativa";
        return count($errores) === 0;
    }

    public function guardar($datos)
    {
        $sql = "INSERT INTO productos (codigo, producto, precio, cantidad) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$datos['codigo'], $datos['producto'], $datos['precio'], $datos['cantidad']]);
    }

    public function editar($id, $datos)
    {
        $sql = "UPDATE productos SET codigo=?, producto=?, precio=?, cantidad=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$datos['codigo'], $datos['producto'], $datos['precio'], $datos['cantidad'], $id]);
    }

    public function obtener($id)
    {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listar($busqueda = '')
    {
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

    public function eliminar($id)
    {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
