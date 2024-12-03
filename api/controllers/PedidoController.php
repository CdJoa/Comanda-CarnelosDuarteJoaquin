<?php
require_once __DIR__ . '/../models/pedido.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';
require_once __DIR__ . '/../models/Tarea.php';

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

    $pedido = new Pedido();
    $pedido->nombreCliente = $nombreCliente;

    $precioTotal = 0;
    $tiempoTotal = 0;

    try {
        $pedido->crearPedido($lista);
        $tarea = new Tarea(); 
        foreach ($lista as $producto) {
            if (is_array($producto)) {
                $tareasAsignadas = $tarea->asignarTrabajador($producto); 
                foreach ($tareasAsignadas as $tareaAsignada) {
                    $tarea = new Tarea();
                    $tarea->setNombreProducto($tareaAsignada['nombre']);
                    $tarea->setCodigoPedido($pedido->codigoPedido);
                    $tarea->trabajadorAsignado = is_array($tareaAsignada['empleadoAsignado']) ? json_encode($tareaAsignada['empleadoAsignado']) : $tareaAsignada['empleadoAsignado'] ?? 'pendiente';

                    $tarea->cantidad = $tareaAsignada['cantidad'] ?? null;
                    $tarea->seccion = Producto::ObtenerSeccionProducto($tareaAsignada['nombre']);
                    $tarea->tiempoEstimado = $tareaAsignada['tiempoEstimado'] ?? 0;

                    if ($tarea->trabajadorAsignado === null) {
                        throw new Exception("El trabajador asignado no puede ser nulo");
                    }

                    $tarea->crearTarea();

                    $precioProducto = Producto::ObtenerPrecioProducto($tareaAsignada['nombre']);
                    $precioTotal += $precioProducto * $tarea->cantidad;
                    $tiempoTotal = Tarea::calcularTiempoFinal($pedido->codigoPedido);
                    
                }
            } else {
                throw new Exception("Producto inválido en la lista: " . json_encode($producto));
            }
        }

        $pedido->precio = $precioTotal;
        $pedido->tiempoEstimado = $tiempoTotal;

        if (Tarea::todosPreparandose($pedido->codigoPedido)) {
            Pedido::marcarComoPreparandose($pedido->codigoPedido);
        }
        $pedido->actualizarPedido();
        
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
            $payload = json_encode($pedido, JSON_PRETTY_PRINT);
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
        $codigoPedido = $parametros['codigoPedido'] ?? null;
        $nombreTrabajador = $parametros['nombreTrabajador'] ?? null;
    
        if (!$codigoPedido || !$nombreTrabajador) {
            $payload = json_encode(["mensaje" => "Faltan parámetros para modificar el producto"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        $tareas = Tarea::obtenerTareas($codigoPedido);
        if (empty($tareas)) {
            $payload = json_encode(["mensaje" => "No se encontraron tareas"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    
        foreach ($tareas as $tarea) {
            if ($tarea->trabajadorAsignado == $nombreTrabajador) {
                Tarea::FinalizarTarea($codigoPedido, $nombreTrabajador); 
                Usuario::cambiarAEstadoLibre($nombreTrabajador);  
                $payload = json_encode(["mensaje" => "Producto finalizado con éxito"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
        }
    
        $payload = json_encode(["mensaje" => "No se encontró al trabajador asignado en la tarea"]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
    


    public function entregarPedido($request, $response, $args)
    {   
        $parametros = $request->getParsedBody();
        $codigoPedido = $parametros['codigoPedido'] ?? null;

        if (Tarea::todosListos($codigoPedido)) {

            $pedido = Pedido::obtenerPedidoCodigo($codigoPedido);
            $pedido->marcarComoEntregado($codigoPedido);
            Mesa::cambiarEstadoMesa($pedido->codigoMesa, 'cliente comiendo');

            $payload = json_encode(["mensaje" => "Pedido entregado con éxito"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $payload = json_encode(["mensaje" => "El pedido no está listo para ser entregado"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    public function pagandoPedido($request, $response, $args){
        $parametros = $request->getParsedBody();
        $codigoPedido = $parametros['codigoPedido'] ?? null;    
        
        try {
            $pedido = Pedido::obtenerPedidoCodigo($codigoPedido);
            
            if (!$pedido) {
                $payload = json_encode(["mensaje" => "Pedido no encontrado"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            if ($pedido['estado'] === 'pagado') {
                $payload = json_encode(["mensaje" => "El pedido ya se encuentra pagado"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            if ($pedido['estado'] !== 'entregado') {
                $payload = json_encode(["mensaje" => "El pedido no ha sido entregado"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
 
            $codigoMesa = $pedido['codigoMesa'];
            Mesa::cambiarEstadoMesa($codigoMesa, 'cliente pagando');	
            Pedido::marcarComoPagado($codigoPedido);
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
        $parametros = $request->getParsedBody();
        $codigoPedido = $parametros['codigoPedido'] ?? null;

        if (!$codigoPedido) {
            $payload = json_encode(["mensaje" => "Código de pedido no proporcionado"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $tareas = Tarea::obtenerTareas($codigoPedido);

            if (empty($tareas)) {
                throw new Exception("No se encontraron tareas.");
            }

            foreach ($tareas as $tarea) {
                if ($tarea->trabajadorAsignado === 'pendiente') {
                    switch ($tarea->seccion) {
                        case 'cocina':
                            $Rol = 'cocinero';
                            break;
                        case 'tragos':
                            $Rol = 'bartender';
                            break;
                        case 'chopera':
                            $Rol = 'cervecero';
                            break;
                        default:
                            throw new Exception("Sección desconocida: " . $tarea->seccion);
                    }
                    $empleadoEncontrado = Usuario::obtener1UsuarioLibresPorRol($Rol);

                    if ($empleadoEncontrado) {
                        $tarea->trabajadorAsignado = $empleadoEncontrado['usuario'];  
                        $tarea->actualizarTrabajador($tarea->id, $empleadoEncontrado['usuario']);
                        Usuario::cambiarAEstadoOcupado($empleadoEncontrado['id']);
                        Pedido::marcarComoPreparandose($codigoPedido);
                        Tarea::PrepararTarea($codigoPedido);
                    } else {
                        throw new Exception("No hay trabajadores disponibles para la sección: " . $tarea->seccion);
                    }
                    break;
                }
            }

            $payload = json_encode(["mensaje" => "Producto asignado con éxito"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }


    public static function MozoSacafoto($request, $response, $args)
    {
        $idPedido = $args['id'] ?? null;

        try {
            $pedido = Pedido::obtenerPedido($idPedido);
            if (!$pedido) {
                throw new Exception("Pedido no encontrado");
            }

            if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("No se proporcionó una foto o hubo un error en la subida");
            }

            $rutaFoto = Pedido::sacarFoto($idPedido);
            $payload = json_encode(["mensaje" => "Foto guardada con éxito", "rutaFoto" => $rutaFoto]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public static function Codigos($request, $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        $codigoMesa = $parsedBody['codigoMesa'] ?? null; 
        $codigoPedido = $parsedBody['codigoPedido'] ?? null; 

        try {
            $pedido = Pedido::obtenerPedidoCodigo($codigoPedido);
            
            if ($pedido && $pedido->codigoMesa === $codigoMesa) {
                $payload = json_encode(["tiempoEstimado" => $pedido->tiempoEstimado . " minutos"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                throw new Exception("No se encontró el pedido o el código de mesa no coincide.");
            }

        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
}
