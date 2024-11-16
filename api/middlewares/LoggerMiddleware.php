<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class LoggerMiddleware
{
    private $rolEsperado;

    public function __construct(string $rolEsperado)
    {
        $this->rolEsperado = $rolEsperado;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $parsedBody = $request->getParsedBody();

        $sector = $parsedBody['sector'] ?? null;
        $usuario = $parsedBody['usuario'] ?? null;
        $clave = $parsedBody['clave'] ?? null;

        if (
            $sector === $this->rolEsperado &&
            Usuario::verificarRol($usuario, $this->rolEsperado) &&
            Usuario::verificarClave($usuario, $clave)
        ) {
            return $handler->handle($request);
        }

        $response = new Response();
        $payload = json_encode(['mensaje' => 'Clave incorrecta o rol no permitido.']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
