
<?php

require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Mesa as Mesa;
use App\Models\AuditoriaAcciones;

class MesaController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        try {

            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $parametros = $request->getParsedBody();

            $estadoRecibido = $parametros['estado'];
            $descripcionRecibida = $parametros['descripcion'];

            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            $codigoAleatorio = substr(str_shuffle($permitted_chars), 0, 10);

            $mesaCreada = new Mesa();

            $mesaCreada->Estado = $estadoRecibido;
            $mesaCreada->Descripcion = $descripcionRecibida;
            $mesaCreada->Codigo = $codigoAleatorio;

            $mesaCreada->save();
            $payload = json_encode(
                array(
                    "IdUsuario" => strval($idUsuarioLogeado),
                    "IdRefUsuario" => null,
                    "IdAccion" =>  strval(AuditoriaAcciones::Alta),
                    "mensaje" => "Mesa creada con éxito",
                    "IdPedido" => null,
                    "Exito" => 1,
                    "IdPedidoDetalle" => null,
                    "IdMesa" => $mesaCreada->IdMesa,
                    "IdProducto" => null,
                    "IdArea" => null,
                    "Hora" => date('h:i:s')
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

    public function BorrarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $idRecibida = $args['IdMesa'];

            $mesaEncontrada = App\Models\Mesa::find($idRecibida);

            if ($mesaEncontrada != null) {
                echo 'aaa';

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => null,
                        "IdAccion" =>  strval(AuditoriaAcciones::Baja),
                        "mensaje" => "Mesa eliminada con éxito",
                        "IdPedido" => null,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $mesaEncontrada->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );
                $mesaEncontrada->delete();
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                throw new Exception("Mesa no encontrada.");
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function ModificarUno($request, $response, $args)
    {
        $id = $args['IdMesa'];

        $mesaEncontrada = App\Models\Mesa::where('IdMesa', '=', $id)->first();

        $body = $request->getParsedBody();

        if ($mesaEncontrada != null) {
            $estado = $body['estado'];
            $descripcion = $body['descripcion'];

            $mesaEncontrada->Estado = $estado;
            $mesaEncontrada->Descripcion = $descripcion;

            $mesaEncontrada->save();
            $payload = json_encode(array("mensaje" => "Mesa modificada"));
        } else {
            $payload = json_encode(array("mensaje" => "Mesa no modificada"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $listaMesas = App\Models\Mesa::all();

        if ($listaMesas == null) {
            $payload = json_encode(array("mensaje" => "No hay ninguna mesa."));
        } else {
            $payload = json_encode(array("listaMesas" => $listaMesas));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $idRecibido = $args['IdMesa'];

        $listaMesas = Mesa::all();
        $mesaEncontrada = $listaMesas->find($idRecibido);

        if ($mesaEncontrada != null) {
            $payload = json_encode($mesaEncontrada);
        } else {
            $payload = json_encode(array("mensaje" => "Mesa no encontrada."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>