<?php
class Pedido{
    public $id;
    public $codigoPedido;
    public $estado;
    public $nombreCliente;
    public $codigoMesa;
    public $tiempoEstimado; 
    public $precio; 
    public $listaProductos = array();

    public function crearPedido($listaProductos)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
    
        do {
            $this->codigoPedido = Pedido::crearCodigo(); 
        } while (Pedido::existeCodigoPedido($this->codigoPedido)); 
    
        $this->codigoMesa = Mesa::obtenerPrimeraMesaAbierta(); 
    
        if ($this->codigoMesa === false) {
            throw new Exception("No hay mesas disponibles.");
        }
    
        $this->estado = 'pendiente';
        $this->tiempoEstimado = 0;
        $this->precio = 0;
    
        $productosJson = json_encode($listaProductos);
    
        error_log("Lista de productos en JSON: " . $productosJson);
    
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigoPedido, nombreCliente, estado, codigoMesa, tiempoEstimado, precio, listaProductos) VALUES (:codigoPedido, :nombreCliente, :estado, :codigoMesa, :tiempoEstimado, :precio, :listaProductos)");
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_INT);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':codigoPedido', $this->codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':listaProductos', $productosJson, PDO::PARAM_STR);
    
        $consulta->execute();
    
        $this->id = $objAccesoDatos->obtenerUltimoId();
    
        Mesa::IngresarCodigoPedidoCambiarEstado($this->codigoMesa, $this->codigoPedido);
    
        return $this->id;
    }
    

        public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigoPedido, estado, nombreCliente, codigoMesa, tiempoEstimado, precio, listaProductos FROM pedidos");
        $consulta->execute();

        $pedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            $pedido['listaProductos'] = json_decode($pedido['listaProductos'], true);
        }

        return $pedidos;
    }


    public static function obtenerPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigoPedido, estado, nombreCliente, codigoMesa, tiempoEstimado, precio, listaProductos FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            $pedido['listaProductos'] = json_decode($pedido['listaProductos'], true);
        }

        return $pedido;
    }

    public static function crearCodigo()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $codigo = '';
        for ($i = 0; $i < 5; $i++) {
            $codigo .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $codigo;
    }

    public static function existeCodigoPedido($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*) FROM pedidos WHERE codigoPedido = :codigoPedido");
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchColumn() > 0; 
    }

    public static function modificarpedido($pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = :estado, codigopedido = :codigopedido, id_pedido = :id_pedido WHERE id = :id");
        
        $consulta->bindValue(':estado', $pedido->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigopedido', $pedido->codigopedido, PDO::PARAM_INT);
        $consulta->bindValue(':id_pedido', $pedido->id_pedido, PDO::PARAM_INT);

        $consulta->execute();
    }

    public static function borrarpedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);

        $consulta->execute();
    }

    public static function asignarSeccionROl($listaProductos)
    {
        foreach ($listaProductos as &$producto) {
            $producto['seccion'] = $seccion
        }
        return $listaProductos;

    }

}
