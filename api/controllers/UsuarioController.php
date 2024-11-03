<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $rol = $parametros['rol'];


        // Creamos el usuario
        $usr = new Usuario();
        $usr->usuario = $usuario;
        $usr->clave = $clave;
        $usr->rol = $rol;

        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre
        $usr = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args){
    $parametros = $request->getParsedBody();
    $id = $request->getQueryParams()['id'] ?? null;


        $usuario = new Usuario();
        $usuario->id = $id; // El ID se obtiene de los parámetros de consulta
        $usuario->usuario = $parametros['usuario'];
        $usuario->clave = $parametros['clave'];
        $usuario->rol = $parametros['rol'];
        
        $respuesta = Usuario::modificarUsuario($usuario);
        
        $payload = json_encode(array("mensaje" => $respuesta));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
{
    $id = $args['id'] ?? null; 
    if ($id !== null) {
        $resultado = Usuario::borrarUsuario($id);
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
?>