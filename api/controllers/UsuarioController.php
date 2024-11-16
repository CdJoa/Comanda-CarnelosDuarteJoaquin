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

        $usr = new Usuario();
        $usr->usuario = $usuario;
        $usr->clave = $clave;
        $usr->rol = $rol;

        try {
            $usr->crearUsuario();
            $payload = json_encode(array("mensaje" => "Usuario creado con éxito"));
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    public function TraerUno($request, $response, $args)
    {
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
        $usuario->id = $id; 
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
    
    public static function pasarALibre($request, $response, $args)
    {
        $usuario = $args['usuario'] ?? null;  

        try {
            $usuarioData = Usuario::obtenerUsuario($usuario);

            if (!$usuarioData) {
                $payload = json_encode(["mensaje" => "Usuario no encontrado"]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            Usuario::cambiarAEstadoLibre($usuarioData->usuario); 

            $payload = json_encode(["mensaje" => "Estado del usuario actualizado a 'libre'"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (Exception $e) {
            $payload = json_encode(["error" => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}
?>