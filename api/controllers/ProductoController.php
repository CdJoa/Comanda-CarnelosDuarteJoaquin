<?php
require_once __DIR__ . '/../models/producto.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';

class ProductoController extends producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $cantidad = $parametros['cantidad'];
        $precioUnidad = $parametros['precioUnidad'];
        $tiempo = $parametros['tiempo'];
        $tipo = $parametros['tipo'];
        $seccion = $parametros['seccion'];


        $prd = new producto();
        $prd->nombre = $nombre;
        $prd->cantidad = $cantidad;
        $prd->precioUnidad = $precioUnidad;
        $prd->tiempo = $tiempo;
        $prd->seccion = $seccion;
        $prd->tipo = $tipo;

        $prd->crearProducto();

        $payload = json_encode(array("mensaje" => "producto creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {        
      $nombre = $args['nombre'];
      $producto = producto::obtenerProducto($nombre);
      $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = producto::obtenerTodos();
        $payload = json_encode(array("listaproducto" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args){
    $parametros = $request->getParsedBody();
    $id = $request->getQueryParams()['id'] ?? null;


        $producto = new producto();
        $producto->id = $id; 
        $producto->nombre = $parametros['nombre'];
        $producto->tiempo = $parametros['tiempo'];
        $producto->cantidad = $parametros['cantidad'];
        $producto->precioUnidad = $parametros['precioUnidad'];
        $producto->seccion = $parametros['seccion'];

        $producto->tipo = $parametros['tipo'];

        $respuesta = producto::modificarproducto($producto);
        
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
      $id = $args['id'] ?? null; 
      if ($id !== null) {
          $resultado = producto::borrarProducto($id);
          if ($resultado) {
            $payload = json_encode(array("mensaje" => "Usuario borrado con éxito"));
        } else {
            $payload = json_encode(array("mensaje" => "Error al borrar el usuario"));
        }
    }
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }
}