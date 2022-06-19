<?php
require_once './models/Usuario.php';
require_once './models/EnumGeneral.php';
require_once './middlewares/AutentificadorJWT.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use \App\Models\Usuario as Usuario;
use \App\Models\EnumGeneral as EnumGeneral;
use App\Models\UsuarioTipo;

class Logger
{
    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }
    public function admin($request, $handler)
    {
        try {
            $header = $request->getHeaderLine('Authorization');
            $response = new Response();

            if (empty($header)) {
                throw new Exception('Es necesario Token para acceso');
            }
            $token = trim(explode("Bearer", $header)[1]);

            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);

            self::ValidarDataToken($data);

            $obj = Usuario::find($data->idUsuario);

            if ($obj->IdUsuarioTipo != UsuarioTipo::Administrador) {
                throw new Exception('Acceso sólo Administradores');
            }

            $obj->MostrarUsuario();
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            $response = $response->withStatus(401);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function adminOrSocio($request, $handler)
    {
        try {
            $header = $request->getHeaderLine('Authorization');
            $response = new Response();

            if (empty($header)) {
                throw new Exception('Es necesario Token para acceso');
            }
            $token = trim(explode("Bearer", $header)[1]);

            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);

            self::ValidarDataToken($data);

            $obj = Usuario::find($data->idUsuario);

            if (!($obj->idUsuarioTipo == UsuarioTipo::Administrador ||
                !$obj->idUsuarioTipo == UsuarioTipo::Socio)) {
                throw new Exception('Acceso sólo Administradores o Socios');
            }


            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            $response = $response->withStatus(401);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function permisos($request, $handler)
    {
        try {
            $header = $request->getHeaderLine('Authorization');
            $response = new Response();
            if (empty($header)) {
                throw new Exception('Es necesario Token para acceso');
            }
            $token = trim(explode("Bearer", $header)[1]);

            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);

            self::ValidarDataToken($data);
            $obj = Usuario::find($data->idUsuario);


            if (!($obj->IdUsuarioTipo == UsuarioTipo::Administrador ||
                $obj->IdUsuarioTipo == UsuarioTipo::Socio ||
                $obj->IdUsuarioTipo == UsuarioTipo::Mozo)) {
                throw new Exception('Acceso sólo Administradores, Socios o Mozos');
            }

            $obj->mostrarUsuario();
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            $response = $response->withStatus(401);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }


    public function usuario($request, $handler)
    {
        try {
            $header = $request->getHeaderLine('Authorization');
            if (empty($header)) {
                throw new Exception('Es necesario Token para acceso');
            }

            $token = trim(explode("Bearer", $header)[1]);
            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);

            $response = new Response();

            self::ValidarDataToken($data);

            $obj = Usuario::find($data->idUsuario);

            $obj->mostrarUsuario();
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            $response = $response->withStatus(401);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    static private function ValidarDataToken($data)
    {
        if (isset($data->idUsuario) && isset($data->usuario) && isset($data->clave)) {
            $obj = Usuario::find($data->idUsuario);
            if ($obj == null) {
                throw new Exception('No existe Usuario');
            }
        } else {
            throw new Exception('Id, Usuario o Clave no fueron seteados en Token');
        }
    }
    public function cocinero($request, $handler)
    {
        try {
            $header = $request->getHeaderLine('Authorization');
            $response = new Response();

            if (empty($header)) {
                throw new Exception('Es necesario Token para acceso');
            }
            $token = trim(explode("Bearer", $header)[1]);

            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);

            self::ValidarDataToken($data);

            $obj = Usuario::find($data->idUsuario);

            if (!($obj->idUsuarioTipo == UsuarioTipo::Administrador ||
                $obj->idUsuarioTipo == UsuarioTipo::Socio ||
                $obj->idUsuarioTipo != UsuarioTipo::Cocinero)) {
                throw new Exception('Acceso sólo Administradores, Socios o Cocineros');
            }

            $obj->mostrarUsuario();
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            $response = $response->withStatus(401);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}
