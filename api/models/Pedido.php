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

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        do {
            $this->codigoPedido = Pedido::crearCodigo(); 
        } while (Pedido::existeCodigoPedido($this->codigoPedido)); 


        Mesa::IngresarIDPedidoCambiarEstado($this->id);

        $this->codigoMesa =  Mesa::ObtenerCodigoMesaPorIDPedido($this->id);

        $this->estado = 'abierta';
        $this->tiempoEstimado = 0;
        $this->precio = 0;


        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos ( codigoPedido,nombreCliente,codigoMesa,estado,tiempoEstimado,precio) VALUES (:codigoPedido,:nombreCliente,:codigoMesa,:estado,:tiempoEstimado,:precio)");
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_INT);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':codigoPedido', $this->codigoPedido, PDO::PARAM_STR);
        
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }




    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigoPedido, estado, nombreCliente, codigoMesa, tiempoEstimado, precio,listaProductos FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($id){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigoPedido, estado, nombreCliente, codigoMesa, tiempoEstimado, precio,listaProductos FROM pedidos");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
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

}
