<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once __DIR__ . '/../controllers/TokenController.php';
class LoggerMiddleware
{
    private $rolEsperado;

    public function __construct(string $rolEsperado)
    {
        $this->rolEsperado = $rolEsperado;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1] ?? '');

        try {
            AutentificadorJWT::VerificarToken($token);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(['mensaje' => 'ERROR: Hubo un error con el TOKEN']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }


        $data = AutentificadorJWT::ObtenerData($token);
        $usuarioM = $data->usuario; 
        $rol = $data->rol;      

        if (
            Usuario::verificarRol($usuarioM, $rol) 
        ) {
            return $handler->handle($request);
        }

        $response = new Response();
        $payload = json_encode(['mensaje' => 'Clave incorrecta o rol no permitido.']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403); 
    }


    public static function verificarToken(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            AutentificadorJWT::VerificarToken($token);
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}