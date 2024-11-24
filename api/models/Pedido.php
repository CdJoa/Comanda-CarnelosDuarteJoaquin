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
    public $fecha; 

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
        $this->fecha = date('Y-m-d');
    
        $productosJson = json_encode($listaProductos);
    
        error_log("Lista de productos en JSON: " . $productosJson);
    
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigoPedido, nombreCliente, estado, codigoMesa, tiempoEstimado, precio, listaProductos, fecha) VALUES (:codigoPedido, :nombreCliente, :estado, :codigoMesa, :tiempoEstimado, :precio, :listaProductos, :fecha)");
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':codigoMesa', $this->codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoEstimado', $this->tiempoEstimado, PDO::PARAM_INT);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':codigoPedido', $this->codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':listaProductos', $productosJson, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->execute();
        $this->id = $objAccesoDatos->obtenerUltimoId();
    
        Mesa::IngresarCodigoPedidoCambiarEstado($this->codigoMesa, $this->codigoPedido);
        Mesa::sumarUso($this->codigoMesa);

    
        return $this->id;
    }
    public static function cuerpoCSV($nombreCliente, $codigoMesa, $estado, $tiempoEstimado, $precio, $codigoPedido,$listaProductos)
    {
        $pedido = new self();
        $pedido->nombreCliente = $nombreCliente;
        $pedido->codigoMesa = $codigoMesa;
        $pedido->estado = $estado;
        $pedido->tiempoEstimado = $tiempoEstimado;
        $pedido->precio = $precio;
        $pedido->codigoPedido = $codigoPedido;
        $pedido->listaProductos = $listaProductos;
    
        return $pedido->crearPedido($listaProductos);
    }
    
        public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
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
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        $pedido = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            $pedido['listaProductos'] = json_decode($pedido['listaProductos'], true);
        }

        return $pedido;
    }

       public static function obtenerPedidoCodigo($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE codigoPedido = :codigoPedido");
        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchObject('Pedido');
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
    public static function asignarTrabajador($listaProductos) {
        foreach ($listaProductos as &$producto) {
            $productoObtenido = Producto::obtenerProducto($producto['nombre']);
            if ($productoObtenido) {
                $seccionProducto = $productoObtenido['seccion'];
                $cantidad = $producto['cantidad'];
                $tiempoEstimado = $productoObtenido['tiempo'];
                $productosAsignados = [];
    
                switch ($seccionProducto) {
                    case 'cocina':
                        for ($i = 0; $i < $cantidad; $i++) {
                            $empleadoAsignado = Usuario::obtener1UsuarioLibresPorRol('cocinero');
                            if ($empleadoAsignado) {
                                $productosAsignados[] = [
                                    'nombre' => $producto['nombre'],
                                    'cantidad' => 1,
                                    'empleadoAsignado' => $empleadoAsignado['usuario'],
                                    'estado' => 'preparandose',
                                    'tiempoEstimado' => $tiempoEstimado * rand(10, 13) / 10
                                ];
                                Usuario::cambiarAEstadoOcupado($empleadoAsignado['id']);
                            } else {
                                $productosAsignados[] = [
                                    'nombre' => $producto['nombre'],
                                    'cantidad' => 1,
                                    'empleadoAsignado' => null,
                                    'estado' => 'pendiente',
                                    'tiempoEstimado' => 'pendiente'
                                ];
                            }
                        }
                        break;
                    case 'chopera':
                    case 'tragos':
                        for ($i = 0; $i < $cantidad; $i++) {
                            if ($i % 5 == 0) {
                                $empleadoAsignado = Usuario::obtener1UsuarioLibresPorRol($seccionProducto == 'chopera' ? 'cervezero' : 'bartender');
                            }
                            if ($empleadoAsignado) {
                                $productosAsignados[] = [
                                    'nombre' => $producto['nombre'],
                                    'cantidad' => min(5, $cantidad - $i),
                                    'empleadoAsignado' => $empleadoAsignado['usuario'],
                                    'estado' => 'preparandose',
                                    'tiempoEstimado' => $tiempoEstimado * rand(10, 13) / 10
                                ];
                                Usuario::cambiarAEstadoOcupado($empleadoAsignado['id']);
                            } else {
                                $productosAsignados[] = [
                                    'nombre' => $producto['nombre'],
                                    'cantidad' => min(5, $cantidad - $i),
                                    'empleadoAsignado' => null,
                                    'estado' => 'pendiente',
                                    'tiempoEstimado' => 'pendiente'
                                ];
                            }
                        }
                        break;
                    default:
                        throw new Exception("Sección de producto no válida.");
                }
    
                $producto['asignaciones'] = $productosAsignados;
            } else {
                throw new Exception("Producto no encontrado: " . $producto['nombre']);
            }
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

    public static function sacarFoto($id){
        $pedido = Pedido::obtenerPedido($id);
        if ($pedido) {
            $foto = $_FILES['foto'];
            $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $nombreFoto = 'Fotos-PDF/pedido_' . $pedido['codigoPedido'] . '.' . $extension;
            move_uploaded_file($foto['tmp_name'], $nombreFoto);
            return $nombreFoto;
        } else {
            throw new Exception("Pedido no encontrado.");
        }
    }
    public static function obtenerPedidosUltimos30Dias()
    {
        $fechaHoy = date('Y-m-d');
        
        $fechaHace30Dias = date('Y-m-d', strtotime('-30 days', strtotime($fechaHoy)));

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM pedidos WHERE fecha >= :fechaHace30Dias");
        $consulta->bindValue(':fechaHace30Dias', $fechaHace30Dias, PDO::PARAM_STR);
        $consulta->execute();
        $ids = $consulta->fetchAll(PDO::FETCH_COLUMN, 0);
        $cantidad = count($ids);

        return [
            'ids' => $ids,
            'cantidad' => $cantidad
        ];
        return $consulta->fetchAll(PDO::FETCH_COLUMN, 0);
    }

}
?>
