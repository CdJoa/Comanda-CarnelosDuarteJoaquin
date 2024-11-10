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

    foreach ($lista as $productos) {
        $nombreProducto = $productos['nombre'];
        
        $producto = Producto::obtenerProducto($nombreProducto);

        if (!$producto) {
            $payload = json_encode(array("mensaje" => "Producto '$nombreProducto' no encontrado"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    $pedido = new Pedido();
    $pedido->nombreCliente = $nombreCliente;
    $pedido->listaProductos = $lista;

    try {
        $pedido->crearPedido($lista);

        $payload = json_encode(array("mensaje" => "Se creó el pedido con éxito"));
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
}
