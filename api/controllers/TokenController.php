<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;
require_once __DIR__ . '/../utils/AutentificadorJWT.php';

class TokenController
{
    public static function crearToken(Request $request): Response
    {
        $parsedBody = $request->getParsedBody();
        $usuario = $parsedBody['usuario'] ?? null;
        $rol = $parsedBody['rol'] ?? null;

        $response = new Response();

        if (Usuario::verificarRol($usuario, $rol)) {
            $datos = ['usuario' => $usuario, 'rol' => $rol];

            $token = AutentificadorJWT::CrearToken($datos);

            $payload = json_encode(['jwt' => $token]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $payload = json_encode(['error' => 'Usuario o rol incorrectos']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }

    public static function verificarToken(Request $request): Response
    {
        $token = $request->getHeader('Authorization')[0] ?? null;

        if (empty($token)) {
            $response = new Response();
            $payload = json_encode(['error' => 'Token no proporcionado']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $data = AutentificadorJWT::ObtenerData($token);

            $tiempoRestante = AutentificadorJWT::ObtenerTiempoRestante($token);

            if ($tiempoRestante > 0) {
                $response = new Response();
                $payload = json_encode(['data' => $data, 'tiempoRestante' => $tiempoRestante]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response = new Response();
                $payload = json_encode(['error' => 'Token ha expirado']);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(['error' => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
/* falta implementar que pasa cuando el token caduco */