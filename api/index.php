<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/MesaController.php';
require_once __DIR__ . '/controllers/PedidoController.php';


// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);
// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->put('/', \UsuarioController::class . ':ModificarUno');
    $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');

    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
    
  });

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->put('/', \ProductoController::class . ':ModificarUno');
    $group->delete('/{id}', \ProductoController::class . ':BorrarUno');

    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{nombre}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno');
    
  });

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->put('/', \MesaController::class . ':ModificarUno');
    $group->put('/cerrarMesa/{codigo}', \MesaController::class . ':cerrarMesa');
    $group->delete('/{id}', \MesaController::class . ':BorrarUno');
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{nombre}', \MesaController::class . ':TraerUno');
    $group->get('/TraerUnoCodigo/{codigo}', \MesaController::class . ':TraerUnoCodigo');  
    $group->post('[/]', \MesaController::class . ':CargarUno');
    
  });


  $app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \PedidoController::class . ':CargarUno');
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->put('/ModificarEstadoProductoEnPedido/{id}', \PedidoController::class . ':ModificarEstadoProductoEnPedido');
    $group->put('/entregarPedido/{id}', \PedidoController::class . ':entregarPedido');
    $group->put('/pagandoPedido/{id}', \PedidoController::class . ':pagandoPedido');

  });

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();