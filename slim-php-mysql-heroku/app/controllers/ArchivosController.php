
<?php
require_once './models/Mesa.php';
require_once './models/Producto.php';
require_once './models/Usuario.php';
require_once './models/Pedido.php';
require_once './models/Archivos.php';
date_default_timezone_set("America/Buenos_Aires");

use App\Models\UsuarioTipo as UsuarioTipo;
use App\Models\Usuario as Usuario;
use App\Models\Producto as Producto;
use App\Models\Auditoria as Auditoria;
use App\Models\AuditoriaAcciones as AuditoriaAcciones;

class ArchivosController
{
    public function CargarProductosCSV($request, $response, $args)
    {
        try {
            $UsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request);
            $header = $request->getHeaderLine('Authorization');

            if ($UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Socio || $UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Administrador) {
                $nombreCSVRecibido = './CSV/' . $_FILES["ProductosCSV"]["name"];

                $nombreCSVReciciboSinExt = explode("./CSV/", $nombreCSVRecibido);
                $destino = $nombreCSVReciciboSinExt[1];
                move_uploaded_file($_FILES["ProductosCSV"]["tmp_name"], $destino);
                $listaProductos = FilesManagement::LeerProductosCSV($destino);

                if (count($listaProductos) > 0) {
                    $payload = json_encode(
                        array(
                            "IdUsuario" => strval($UsuarioLogeado->IdUsuario),
                            "IdRefUsuario" => $UsuarioLogeado->IdUsuario,
                            "IdAccion" =>  strval(AuditoriaAcciones::CargaDatos),
                            "mensaje" => "Se cargaron :" . count($listaProductos) . ' productos.',
                            "IdPedido" => null,
                            "IdPedidoDetalle" => null,
                            "IdMesa" => null,
                            "IdProducto" => null,
                            "IdArea" => null,
                            "Hora" => date('h:i:s')
                        )
                    );
                }
            }
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function DescargarProductosCSV($request, $response, $args)
    {
        $UsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request);
        $header = $request->getHeaderLine('Authorization');

        if ($UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Socio || $UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Administrador) {
            $listaDB = Producto::all();

            if (count($listaDB) > 0) {
                try {
                    FilesManagement::ProductosToCSV('ProductosDescarga.csv', json_decode($listaDB));

                    $payload = json_encode(
                        array(
                            "IdUsuario" => strval($UsuarioLogeado->IdUsuario),
                            "IdRefUsuario" => $UsuarioLogeado->IdUsuario,
                            "IdAccion" =>  strval(AuditoriaAcciones::DescargaDatos),
                            "mensaje" => "Descarga de datos de productos",
                            "IdPedido" => null,
                            "IdPedidoDetalle" => null,
                            "IdMesa" => null,
                            "IdProducto" => null,
                            "IdArea" => null,
                            "Hora" => date('h:i:s')
                        )
                    );
                } catch (Exception $e) {
                    $payload = json_encode(array(
                        "mensajeFinal" => "La descarga de productos no fue realizada. No hay ningun producto en DB.",
                    ));
                }
            }
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function DescargarAuditoriaCSV($request, $response, $args)
    {
        $UsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request);
        $header = $request->getHeaderLine('Authorization');

        if ($UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Socio || $UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Administrador) {
            $listaDB = Auditoria::all();
            if (count($listaDB) > 0) {
                try {
                    FilesManagement::AuditoriaToCSV('AuditoriaDescarga.csv', json_decode($listaDB));

                    $payload = json_encode(
                        array(
                            "IdUsuario" => strval($UsuarioLogeado->IdUsuario),
                            "IdRefUsuario" => null,
                            "IdAccion" =>  strval(AuditoriaAcciones::DescargaDatos),
                            "mensaje" => "Descarga de datos de productos",
                            "IdPedido" => null,
                            "IdPedidoDetalle" => null,
                            "IdMesa" => null,
                            "IdProducto" => null,
                            "IdArea" => null,
                            "Hora" => date('h:i:s')
                        )
                    );
                } catch (Exception $e) {
                    $payload = json_encode(array(
                        "mensajeFinal" => "La descarga de auditoria no fue realizada. No hay datos en DB.",
                    ));
                }
            }
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function DescargarAuditoriaCSVPorId($request, $response, $args)
    {
        $UsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request);
        $header = $request->getHeaderLine('Authorization');
        $id = $args["IdUsuario"];
        $usuario = Usuario::find($id)->first();
        if ($UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Socio || $UsuarioLogeado->IdUsuarioTipo == UsuarioTipo::Administrador) {
            $listaDB = Auditoria::all();
            if (count($listaDB) > 0) {
                try {
                    FilesManagement::AuditoriaToCSV('AuditoriaDescarga.Id' . strval($usuario->IdUsuario) . '.csv', json_decode($listaDB), $id);

                    $payload = json_encode(
                        array(
                            "IdUsuario" => strval($UsuarioLogeado->IdUsuario),
                            "IdRefUsuario" => strval($usuario->IdUsuario),
                            "IdAccion" =>  strval(AuditoriaAcciones::DescargaDatos),
                            "mensaje" => "Descarga de datos de auditoria por id",
                            "IdPedido" => null,
                            "IdPedidoDetalle" => null,
                            "IdMesa" => null,
                            "IdProducto" => null,
                            "IdArea" => null,
                            "Hora" => date('h:i:s')
                        )
                    );
                } catch (Exception $e) {
                    $payload = json_encode(array(
                        "mensajeFinal" => "La descarga de productos no fue realizada. No hay ningun producto en DB.",
                    ));
                }
            }
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>
