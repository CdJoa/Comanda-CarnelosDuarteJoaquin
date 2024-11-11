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
        $this->tiempoEstimado = Pedido::CalcularTiempoEstimado($listaProductos);
        $this->precio = Pedido::CalcularPrecio($listaProductos);
    
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

    public static function asignarTrabajadorAProducto($nombreProducto, $cantidad) {
        $producto = Producto::obtenerProducto($nombreProducto);
    
        if ($producto) {
            $seccionProducto = $producto['seccion'];
            $empleadoAsignado = null;
    
            switch ($seccionProducto) {
                case 'cocina':
                    $empleadoAsignado = Usuario::obtener1UsuarioLibresPorRol('cocinero');
                    break;
                case 'chopera':
                    $empleadoAsignado = Usuario::obtener1UsuarioLibresPorRol('cervezero');
                    break;
                case 'tragos':
                    $empleadoAsignado = Usuario::obtener1UsuarioLibresPorRol('bartender');
                    break;
                default:
                    throw new Exception("Sección de producto no válida.");
            }
    
            if ($empleadoAsignado) {
                $producto['empleadoAsignado'] = $empleadoAsignado['usuario']; 
                Usuario::cambiarAEstadoOcupado($empleadoAsignado['id']);
                return [
                    'nombre' => $producto['nombre'],  
                    'cantidad' => $cantidad,         
                    'empleadoAsignado' => $empleadoAsignado['usuario'],
                    'estado' => 'preparandose'
                ];        
            } else {
                throw new Exception("No hay empleados disponibles para la sección: $seccionProducto.");
            }
        } else {
            throw new Exception("Producto no encontrado: $nombreProducto.");
        }
    }
    public static function asignarTrabajador($listaProductos) {
        foreach ($listaProductos as &$producto) {
            $producto = self::asignarTrabajadorAProducto($producto['nombre'], $producto['cantidad']);
        }
        return $listaProductos;
    }

    public static function CalcularTiempoEstimado($listaProductos)
    {
        $tiempoMaximo = 0;

        foreach ($listaProductos as $producto) {
            $productoObtenido = Producto::obtenerProducto($producto['nombre']);
            
            if ($productoObtenido) {
                $tiempoPreparacion = $productoObtenido['tiempo'];
                
                $tiempoPreparacionAjustado = $tiempoPreparacion * 1.4;

                if ($tiempoPreparacionAjustado > $tiempoMaximo) {
                    $tiempoMaximo = $tiempoPreparacionAjustado;
                }
            } else {
                throw new Exception("Producto no encontrado: " . $producto['nombre']);
            }
        }

        return $tiempoMaximo; 
    }

    public static function CalcularPrecio($listaProductos)
    {
        $precioAcumulado = 0;

        foreach ($listaProductos as $producto) {
            $productoObtenido = Producto::obtenerProducto($producto['nombre']);
            
            if ($productoObtenido) {
                $precioProducto = $productoObtenido['precioUnidad'];
                $precioAcumulado += $precioProducto * $producto['cantidad'];
            } else {
                throw new Exception("Producto no encontrado: " . $producto['nombre']);
            }
        }
        return $precioAcumulado; 
    }


    public static function marcarComoEntregado($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = 'entregado' WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }
    public static function marcarComoPagado($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = 'pagado' WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }
}
?>
