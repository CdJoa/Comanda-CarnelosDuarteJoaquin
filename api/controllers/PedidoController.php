<?php
require_once __DIR__ . '/../models/pedido.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
    
        $nombreCliente = $parametros['nombreCliente'];
        $lista = $parametros['listaProductos'];
    
        if (empty($lista)) {
            $payload = json_encode(array("mensaje" => "No se proporcionaron productos"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    
        try {
            $listaConTrabajadores = Pedido::asignarTrabajador($lista);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        $pedido = new Pedido();
        $pedido->nombreCliente = $nombreCliente;
        $pedido->listaProductos = $listaConTrabajadores;
        
    
        try {
            $pedido->crearPedido($listaConTrabajadores);
            $payload = json_encode(array("mensaje" => "Se creó el pedido con éxito", "pedido" => $pedido));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    public function TraerUno($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        if ($id) {
            $pedido = pedido::obtenerpedido($id);
            $payload = json_encode($pedido);
        } else {
            $payload = json_encode(array("mensaje" => "ID no proporcionado"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = pedido::obtenerTodos();
        $payload = json_encode(array("listapedido" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $request->getQueryParams()['id'] ?? null;

        if ($id) {
            $pedido = new pedido();
            $pedido->id = $id;
            $pedido->estado = $parametros['estado'];
            $pedido->codigoMesa = $parametros['codigoMesa'];

            $respuesta = pedido::modificarpedido($pedido);
            $payload = json_encode(array("mensaje" => $respuesta));
        } else {
            $payload = json_encode(array("mensaje" => "Faltan datos para modificar el pedido"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarEstadoProductoEnPedido($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idPedido = $args['id'] ?? null;
        $nombreProducto = $parametros['nombreProducto'] ?? null;
        $estadoProducto = $parametros['estadoProducto'] ?? null;
        $nombreTrabajador = $parametros['nombreTrabajador'] ?? null;

        if (!$idPedido || !$nombreProducto || !$estadoProducto || !$nombreTrabajador) {
            $payload = json_encode(["mensaje" => "Faltan parámetros para modificar el producto"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $pedido = Pedido::obtenerPedido($idPedido);
            if (!$pedido) {
                throw new Exception("Pedido no encontrado");
            }

            $productoEncontrado = false;

            foreach ($pedido['listaProductos'] as &$producto) {
                if ($producto['nombre'] === $nombreProducto) {
                    if ($producto['empleadoAsignado'] !== $nombreTrabajador) {
                        $payload = json_encode(["mensaje" => "El trabajador no está asignado a este producto"]);
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);  // Forbidden
                    }

                    $producto['estado'] = $estadoProducto;
                    $productoEncontrado = true;
                    break;
                }
            }

            if (!$productoEncontrado) {
                throw new Exception("Producto pendiente no encontrado en el pedido");
            }

            $productosJson = json_encode($pedido['listaProductos']);
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET listaProductos = :listaProductos WHERE id = :id");
            $consulta->bindValue(':listaProductos', $productosJson, PDO::PARAM_STR);
            $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
            $consulta->execute();

            $payload = json_encode(["mensaje" => "Producto actualizado con éxito", "producto" => $producto]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                
        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function entregarPedido($request, $response, $args)
    {
        $idPedido = $args['id'] ?? null;
        
        if (!$idPedido) {
            $payload = json_encode(["mensaje" => "ID del pedido no proporcionado"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        try {
            $pedido = Pedido::obtenerPedido($idPedido);
            
            if (!$pedido) {
                $payload = json_encode(["mensaje" => "Pedido no encontrado"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
    
            foreach ($pedido['listaProductos'] as $producto) {
                if ($producto['estado'] !== 'listo') {
                    $payload = json_encode(["mensaje" => "No todos los productos están listos para ser entregados"]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
            }
    
            $codigoMesa = $pedido['codigoMesa'];
            Mesa::cambiarEstadoMesa($codigoMesa, 'cliente comiendo');	
            foreach ($pedido['listaProductos'] as $producto) {
                $empleadoAsignado = $producto['empleadoAsignado'];  
                Usuario::cambiarAEstadoLibre($empleadoAsignado);

            }
    
            Pedido::marcarComoEntregado($idPedido);
    
            $payload = json_encode(["mensaje" => "Pedido entregado con éxito y empleados asignados actualizados"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    public function pagandoPedido($request, $response, $args){
        $idPedido = $args['id'] ?? null;
        
        if (!$idPedido) {
            $payload = json_encode(["mensaje" => "ID del pedido no proporcionado"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        try {
            $pedido = Pedido::obtenerPedido($idPedido);
            
            if (!$pedido) {
                $payload = json_encode(["mensaje" => "Pedido no encontrado"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            if ($pedido['estado'] !== 'entregado') {
                $payload = json_encode(["mensaje" => "El pedido no ha sido entregado"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            $codigoMesa = $pedido['codigoMesa'];
            Mesa::cambiarEstadoMesa($codigoMesa, 'cliente pagando');	
            Pedido::marcarComoPagado($idPedido);
    
            $payload = json_encode(["mensaje" => "Pedido entregado con éxito y empleados asignados actualizados"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        if ($id) {
            $resultado = pedido::borrarpedido($id);
            if ($resultado) {
                $payload = json_encode(array("mensaje" => "pedido borrada con éxito"));
            } else {
                $payload = json_encode(array("mensaje" => "Error al borrar la pedido"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "ID no proporcionado"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function asignarProductoPendiente($request, $response, $args)
    {
        $idPedido = $args['id'] ?? null;

        if (!$idPedido) {
            $payload = json_encode(["mensaje" => "ID del pedido no proporcionado"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        try {
            $pedido = Pedido::obtenerPedido($idPedido);
    
            $productoPendienteEncontrado = false;
            foreach ($pedido['listaProductos'] as &$producto) {
                if ($producto['estado'] === 'pendiente') {
                    $productoPendienteEncontrado = true;
                    $producto['empleadoAsignado'] = Pedido::asignarTrabajador([$producto])[0]['empleadoAsignado'];
                    Pedido::CalcularTiempoEstimado([$producto]);
                    $producto['estado'] = 'preparandose'; 
                }
            }
    
            if (!$productoPendienteEncontrado) {
                $payload = json_encode(["mensaje" => "No hay productos pendientes en el pedido"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
    
            $productosJson = json_encode($pedido['listaProductos']);
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET listaProductos = :listaProductos WHERE id = :id");
            $consulta->bindValue(':listaProductos', $productosJson, PDO::PARAM_STR);
            $consulta->bindValue(':id', $idPedido, PDO::PARAM_INT);
            $consulta->execute();
    
            $payload = json_encode(["mensaje" => "Pedido asignado y actualizado con éxito", "pedido" => $pedido]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
