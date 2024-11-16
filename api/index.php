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
require_once './middlewares/LoggerMiddleware.php';

require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/MesaController.php';
require_once __DIR__ . '/controllers/PedidoController.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();


$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->put('/', \UsuarioController::class . ':ModificarUno');
    $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');

    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->put('/pasarALibre/{usuario}', \UsuarioController::class . ':pasarALibre');

    $group->post('[/]', \UsuarioController::class . ':CargarUno');

  })->add(new \LoggerMiddleware('socio'));

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->put('/', \ProductoController::class . ':ModificarUno');
    $group->delete('/{id}', \ProductoController::class . ':BorrarUno');
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->get('/{nombre}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno');
    
  })->add(new \LoggerMiddleware('socio'));


$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->put('/', \MesaController::class . ':ModificarUno');

    $group->put('/cerrarMesa/{codigo}', \MesaController::class . ':cerrarMesa');

    $group->delete('/{id}', \MesaController::class . ':BorrarUno');
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{nombre}', \MesaController::class . ':TraerUno');
    $group->get('/TraerUno/{id}', \MesaController::class . ':TraerUno');  
    $group->post('[/]', \MesaController::class . ':CargarUno');
    
  })->add(new \LoggerMiddleware('socio'));


  
    $app->group('/pedidos', function (RouteCollectorProxy $group) {
      $group->post('[/]', \PedidoController::class . ':CargarUno')->add(new \LoggerMiddleware('mozo'));
  
      $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(new \LoggerMiddleware('mozo'));
      $group->get('/{id}', \PedidoController::class . ':TraerUno')->add(new \LoggerMiddleware('mozo'));
  
      $group->put('/ModificarEstadoProductoEnPedido/{id}', \PedidoController::class . ':ModificarEstadoProductoEnPedido')
            ->add(new \LoggerMiddleware('bartender'))
            ->add(new \LoggerMiddleware('cocinero'))
            ->add(new \LoggerMiddleware('cervezero'));

      $group->put('/asignarProductoPendiente/{id}', \PedidoController::class . ':asignarProductoPendiente')->add(new \LoggerMiddleware('mozo'));
  
      $group->put('/entregarPedido/{id}', \PedidoController::class . ':entregarPedido')->add(new \LoggerMiddleware('mozo'));
      $group->put('/pagandoPedido/{id}', \PedidoController::class . ':pagandoPedido')->add(new \LoggerMiddleware('mozo'));
  
    });

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();