<?php
date_default_timezone_set("America/Buenos_Aires");
require_once './models/Usuario.php';
require_once './models/EnumGeneral.php';

use App\Models\AuditoriaAcciones;
use \App\Models\Usuario as Usuario;
use \App\Models\EnumGeneral as EnumGeneral;
use App\Models\UsuarioTipo;

class LoginController
{

    private static function ValidarDatosLogin($usuario, $clave)
    {
        if (!isset($usuario) || !isset($clave)) {
            throw new Exception("Usuario o Clave indefinidos.");
        }
    }

    public function access($request, $response, $args)
    {
        try {
            $params = $request->getParsedBody();
            $usuarioNombre = $params['usuario'];
            $clave = $params['clave'];

            self::ValidarDatosLogin($usuarioNombre, $clave);
            $obj = Usuario::where('usuario', $usuarioNombre)->first();

            if ($obj == null) {
                throw new Exception("No existe Usuario con nombre '$usuarioNombre'");
            }
            if ($obj->Clave !== $clave) {
                throw new Exception("Clave incorrecta");
            }

            $datos = array('idUsuario' => $obj->IdUsuario, 'usuario' => $obj->Nombre, 'clave' => $obj->Clave);
            $token = AutentificadorJWT::CrearToken($datos);

            $obj->MostrarUsuario();
            $payload = json_encode(
                array(
                    'jwt' => $token,
                    "idUsuario" => $obj->IdUsuario,
                    "idAccion" => AuditoriaAcciones::Login,
                    "mensaje" => "Login Usuario con éxito",
                    "idProducto" => $obj->IdProducto,
                    "idPedido" => $obj->IdPedido,
                    "idMesa" => $obj->IdMesa,
                    "idArea" => $obj->IdArea,
                    "hora" => date('h:i:s')
                )
            );
            $response->getBody()->write($payload);

            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}
