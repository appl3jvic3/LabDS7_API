<?php
require_once __DIR__ . '/Database.php';

class Producto
{
    private $db;
    private $tabla = "productos";

    public function __construct()
    {
        $this->db = (new Database())->conexion;
    }

<<<<<<< HEAD:LabAPI_DS7/clases/Producto.php
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
=======
    // Guardar producto
    public function guardar($datos) {
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/clases/Producto.php
        $sql = "INSERT INTO productos (codigo, producto, precio, cantidad) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$datos['codigo'], $datos['producto'], $datos['precio'], $datos['cantidad']]);
    }

<<<<<<< HEAD:LabAPI_DS7/clases/Producto.php
    public function editar($id, $datos)
    {
=======
    // Actualizar producto
    public function editar($id, $datos) {
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/clases/Producto.php
        $sql = "UPDATE productos SET codigo=?, producto=?, precio=?, cantidad=? WHERE id=?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$datos['codigo'], $datos['producto'], $datos['precio'], $datos['cantidad'], $id]);
    }

<<<<<<< HEAD:LabAPI_DS7/clases/Producto.php
    public function obtener($id)
    {
=======
    // Obtener un producto por ID
    public function obtener($id) {
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/clases/Producto.php
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

<<<<<<< HEAD:LabAPI_DS7/clases/Producto.php
    public function listar($busqueda = '')
    {
=======
    // Listar todos los productos (para búsqueda y listado)
    public function listar($busqueda = '') {
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/clases/Producto.php
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

<<<<<<< HEAD:LabAPI_DS7/clases/Producto.php
    public function eliminar($id)
    {
=======
    // Eliminar producto
    public function eliminar($id) {
>>>>>>> parent of 08c5dea (Aicion Proyecto):ProyectoCRUDAPI/clases/Producto.php
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
