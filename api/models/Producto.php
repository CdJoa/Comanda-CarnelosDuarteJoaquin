<?php

class Producto
{
    public $id;
    public $nombre;
    public $cantidad;
    public $precioUnidad;
    public $tiempo;
    public $tipo;
    public $seccion;


    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
    
        $productoExistente = self::obtenerProducto($this->nombre);
    
        if ($productoExistente) {
            $nuevaCantidad = $productoExistente['cantidad'] + $this->cantidad;
            $nuevoPrecio = $this->precioUnidad; 
            $consulta = $objAccesoDatos->prepararConsulta(
                "UPDATE productos SET cantidad = :cantidad, precioUnidad = :precioUnidad WHERE id = :id"
            );
            $consulta->bindValue(':cantidad', $nuevaCantidad, PDO::PARAM_INT);
            $consulta->bindValue(':precioUnidad', $nuevoPrecio, PDO::PARAM_INT);
            $consulta->bindValue(':id', $productoExistente['id'], PDO::PARAM_INT);
            $consulta->execute();
    
            return $productoExistente['id']; 
        } else {
            $consulta = $objAccesoDatos->prepararConsulta(
                "INSERT INTO productos (nombre, cantidad, precioUnidad, tiempo, tipo, seccion) 
                 VALUES (:nombre, :cantidad, :precioUnidad, :tiempo, :tipo, :seccion)"
            );
            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
            $consulta->bindValue(':precioUnidad', $this->precioUnidad, PDO::PARAM_INT);
            $consulta->bindValue(':tiempo', $this->tiempo, PDO::PARAM_INT);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':seccion', $this->seccion, PDO::PARAM_STR);
            $consulta->execute();
    
            return $objAccesoDatos->obtenerUltimoId();
        }
    }
    

    public static function cuerpoCSV($nombre, $cantidad, $precioUnidad, $tiempo, $tipo, $seccion)
    {
        $producto = new self();
        $producto->nombre = $nombre;
        $producto->cantidad = $cantidad;
        $producto->precioUnidad = $precioUnidad;
        $producto->tiempo = $tiempo;
        $producto->tipo = $tipo;
        $producto->seccion = $seccion;

        return $producto->crearProducto();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre,cantidad,precioUnidad,tipo,seccion,tiempo FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'producto');
    }

    public static function obtenerProductosPorSeccion($seccion)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre,cantidad,precioUnidad,tipo,seccion,tiempo FROM productos WHERE seccion = :seccion");
        $consulta->bindValue(':seccion', $seccion, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'producto');
    }

    public static function obtenerProducto($nombre)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC); 
    }

    public static function modificarProducto($producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos SET nombre = :nombre, cantidad = :cantidad, precioUnidad = :precioUnidad, tipo = :tipo, seccion = :seccion, tiempo = :tiempo WHERE id = :id");
        
        $consulta->bindValue(':nombre', $producto->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':id', $producto->id, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $producto->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':precioUnidad', $producto->precioUnidad, PDO::PARAM_INT);
        $consulta->bindValue(':tipo', $producto->tipo, PDO::PARAM_STR);
        $consulta->bindValue(':seccion', $producto->seccion, PDO::PARAM_STR);

        $consulta->bindValue(':tiempo', $producto->tiempo, PDO::PARAM_INT); 

        $consulta->execute();
    }

    public static function borrarProducto($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);

        $consulta->execute();
    }
}
?>