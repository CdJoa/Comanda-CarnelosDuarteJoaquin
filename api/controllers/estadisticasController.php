<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Usuario.php';

class estadisticasController
{
    public static function obtenerPedidosUltimos30Dias(Request $request, Response $response): Response
    {
        $pedidos = Pedido::obtenerPedidosUltimos30Dias();
        $response->getBody()->write(json_encode($pedidos, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public static function obtenerUsuariossAltaUltimos30Dias(Request $request, Response $response): Response
    {
        $pedidos = Usuario::obtenerUsuariosAlta30Dias();
        $response->getBody()->write(json_encode($pedidos, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
?>
