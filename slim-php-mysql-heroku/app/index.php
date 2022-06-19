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
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/AutentificadorJWT.php';
require_once './middlewares/Historial.php';
require_once './middlewares/Logger.php'; 

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/LoginController.php';
require_once './controllers/ArchivosController.php';
// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$cointainer = $app->getContainer();

$capsule = new Capsule();

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();;


$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('[/]', \LoginController::class . ':access'); 
  })->add(\Historial::class . ':altaAccion');
  

// ------------------------// U S U A R I O S // ------------------------
$app->group('/usuarios', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \UsuarioController::class . ':TraerTodos')->add(\Logger::class . ':adminOrSocio');

    $group->get('/{IdUsuario}', \UsuarioController::class . ':TraerUno')->add(\Logger::class . ':adminOrSocio');
    
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(\Logger::class . ':admin');

    $group->put('/{IdUsuario}', \UsuarioController::class. ':ModificarUno')->add(\Logger::class . ':admin');

    $group->delete('/{IdUsuario}', \UsuarioController::class . ':BorrarUno')->add(\Logger::class . ':admin');
    })->add(\Historial::class . ':altaAccion');
// ------------------------// MESAS// ------------------------------
$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \MesaController::class . ':TraerTodos')->add(\Logger::class . ':permisos');  

    $group->get('/{IdMesa}', \MesaController::class . ':TraerUno')->add(\Logger::class . ':permisos');  

    $group->post('[/]', \MesaController::class . ':CargarUno')->add(\Logger::class . ':permisos');  

    $group->put('/{IdMesa}', \MesaController::class. ':ModificarUno')->add(\Logger::class . ':permisos');  

    $group->delete('/{IdMesa}', \MesaController::class . ':BorrarUno')->add(\Logger::class . ':permisos');  
    })->add(\Historial::class . ':altaAccion');

// ------------------------// P R O D U C T O S // ------------------
$app->group('/productos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \ProductoController::class . ':TraerTodos')->add(\Logger::class . ':permisos');  

    $group->get('/{IdProducto}', \ProductoController::class . ':TraerUno')->add(\Logger::class . ':permisos');  

    $group->post('[/]', \ProductoController::class . ':CargarUno')->add(\Logger::class . ':adminOrSocio');

    $group->put('/{IdProducto}', \ProductoController::class. ':ModificarUno')->add(\Logger::class . ':adminOrSocio');

    $group->delete('/{IdProducto}', \ProductoController::class . ':BorrarUno')->add(\Logger::class . ':adminOrSocio');
     })->add(\Historial::class . ':altaAccion');

// ------------------------// P E D I D O S // ------------------
$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \PedidoController::class . ':TraerTodos')->add(\Logger::class . ':permisos');  
    
    $group->get('/{IdPedido}', \PedidoController::class . ':TraerUno')->add(\Logger::class . ':permisos');  

    $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\Logger::class . ':permisos');  
    
    $group->put('/{IdPedido}', \PedidoController::class. ':ModificarUno')->add(\Logger::class . ':permisos');  

    $group->delete('/{IdPedido}', \PedidoController::class . ':BorrarUno')->add(\Logger::class . ':permisos');  
})->add(\Historial::class . ':altaAccion');

//----------------------------------------------------------------
// ------------------------// A R C H I V O S // ------------------
$app->group('/archivos', function (RouteCollectorProxy $group) 
{
    $group->post('/productosleer', \ArchivosController::class . ':DescargarProductosCSV')->add(\Logger::class . ':adminOrSocio');
    $group->post('/productoscargar', \ArchivosController::class . ':CargarProductosCSV')->add(\Logger::class . ':adminOrSocio');

     $group->get('/auditoria', \ArchivosController::class . ':DescargarAuditoriaCSV')->add(\Logger::class . ':adminOrSocio');
     $group->get('/auditoria/{IdUsuario}', \ArchivosController::class . ':DescargarAuditoriaCSVPorId')->add(\Logger::class . ':adminOrSocio');
    
})->add(\Historial::class . ':altaAccion');
//----------------------------------------------------------------
$app->get('[/]', function (Request $request, Response $response) 
{    
    $response->getBody()->write("Slim Framework 4 PHP Stefano :D");
    return $response;
});

$app->run();
