<?php
require_once __DIR__ . '/../models/Mesa.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
            $mesa = new Mesa();

            $mesa->crearmesa();
            if ($mesa) {
                $payload = json_encode(array("mensaje" => "Se creo mesa con exito"));
            } else {
                $payload = json_encode(array("mensaje" => "Error al crear la mesa"));
            }

            $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cerrarMesa($request, $response, $args)
    {
        $codigo = $args['codigo'] ?? null;
        if ($codigo) {
            Mesa::cambiarEstadoMesa($codigo, 'cerrada');	
            $payload = json_encode(array("mensaje" => "Mesa cerrada con éxito"));
        } else {
            $payload = json_encode(array("mensaje" => "Código no proporcionado"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        if ($id) {
            $mesa = Mesa::obtenermesa($id);
            $payload = json_encode($mesa);
        } else {
            $payload = json_encode(array("mensaje" => "ID no proporcionado"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listamesa" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $request->getQueryParams()['id'] ?? null;

        if ($id ) {
            $mesa = new Mesa();
            $mesa->id = $id;
            $mesa->estado = $parametros['estado'];
            $mesa->codigoMesa = $parametros['codigoMesa'];

            $respuesta = Mesa::modificarmesa($mesa);
            $payload = json_encode(array("mensaje" => $respuesta));
        } else {
            $payload = json_encode(array("mensaje" => "Faltan datos para modificar la mesa"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['id'] ?? null;
        if ($id) {
            $resultado = Mesa::borrarmesa($id);
            if ($resultado) {
                $payload = json_encode(array("mensaje" => "Mesa borrada con éxito"));
            } else {
                $payload = json_encode(array("mensaje" => "Error al borrar la mesa"));
            }
        } else {
            $payload = json_encode(array("mensaje" => "ID no proporcionado"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
