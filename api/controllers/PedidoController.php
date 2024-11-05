<?php
require_once __DIR__ . '/../models/pedido.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombreCliente = $parametros['nombreCliente'];
            $pedido = new pedido();
            $pedido->nombreCliente = $nombreCliente;
            $pedido->crearpedido();
            if ($pedido) {
                $payload = json_encode(array("mensaje" => "Se creo pedido con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Error al crear la pedido"));
            }

            $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
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
