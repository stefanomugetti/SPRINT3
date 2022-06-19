<?php
date_default_timezone_set("America/Buenos_Aires");
require_once './models/Pedido.php';
require_once './models/PedidoDetalle.php';
require_once './interfaces/IApiUsable.php';
require_once './models/Producto.php';



use \App\Models\Pedido as Pedido;
use \App\Models\PedidoDetalle as PedidoDetalle;
use App\Models\AuditoriaAcciones;
use \App\Models\Producto as Producto;
use \App\Models\Usuario as Usuario;
use \App\Models\Mesa as Mesa;


class PedidoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');

            $body = json_decode(file_get_contents("php://input"), true);
            $idMesa = $body['IdMesa'];
            $nombreCliente = $body['nombreCliente'];
            $pathFoto = $body['pathFoto'];
            $productosPedidos = $body['productosPedidos'];

            $mesaEncontrada = Mesa::find($idMesa);
            if ($mesaEncontrada != null) {
                $usuario = Usuario::find($idUsuarioLogeado);
                if ($usuario != null  || $usuario["Estado"] != 'Ocupado') {

                    $importeParcial = 0;
                    $flag = false;

                    if (count($productosPedidos) > 0) {

                        foreach ($productosPedidos as $productoPostman) {
                            $producto = Producto::where('IdProducto', '=', $productoPostman["idProducto"])->first();
                            if ($producto != null) {
                                $cantidad = $productoPostman["cantidad"];
                                if (intval($producto->Stock) >= intval($productoPostman["cantidad"])) {
                                    echo 'e';
                                    $importeParcial += $producto->PrecioUnidad * $cantidad;
                                    //Saco el tiempo estimado del pedido
                                    if (!$flag)
                                        $tiempoEstimado = $producto->TiempoEspera;
                                    if ($producto->TiempoEspera > $tiempoEstimado)
                                        $tiempoEstimado = $producto->TiempoEspera;
                                } else { //NO HAY STOCK DEL PRODUCTO
                                    $response->getBody()->write('No hay stock del producto pedido.');
                                    return $response->withHeader('Content-Type', 'application/json');
                                }
                            } else { //PRODUCTO NO EXISTE
                                $response->getBody()->write('El producto no esta disponible. <br>Revise el menu.');
                                return $response->withHeader('Content-Type', 'application/json');
                                break;
                            }
                        }
                        //-------------------------------- CREACION DEL PEDIDO ---------------------------------------------//

                        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                        $codigoAlfanumericoCreado = substr(str_shuffle($permitted_chars), 0, 10);

                        $pedidoCreado = new Pedido();

                        $pedidoCreado->CodigoPedido = $codigoAlfanumericoCreado;
                        $pedidoCreado->Estado = "Pendiente";
                        $pedidoCreado->ImporteTotal = $importeParcial;
                        $pedidoCreado->TiempoPreparacion = $tiempoEstimado;
                        $pedidoCreado->NombreCliente = $nombreCliente;
                        $pedidoCreado->IdMesa = $mesaEncontrada->IdMesa;
                        $pedidoCreado->IdUsuario = $usuario->IdUsuario;
                        $pedidoCreado->PathFoto = $pathFoto;

                        $pedidoCreado->save();

                        $payload = json_encode(
                            array(
                                "IdUsuario" => strval($idUsuarioLogeado),
                                "IdRefUsuario" => $pedidoCreado->IdUsuario,
                                "IdAccion" =>  strval(AuditoriaAcciones::Alta),
                                "mensaje" => "Pedido creado con éxito",
                                "IdPedido" => $pedidoCreado->IdPedido,
                                "IdPedidoDetalle" => null,
                                "IdMesa" => $pedidoCreado->IdMesa,
                                "IdProducto" => null,
                                "IdArea" => null,
                                "Hora" => date('h:i:s')
                            )
                        );

                        foreach ($productosPedidos as $producto2) {
                            $pedidoDetalle = new PedidoDetalle();
                            $pedidoDetalle->Cantidad = $producto2["cantidad"];
                            $pedidoDetalle->Estado = "preparandose";
                            $pedidoDetalle->IdProducto = $producto2["idProducto"];
                            $pedidoDetalle->IdPedido = $pedidoCreado->IdPedido;
                            $pedidoDetalle->save();
                            //RESTO STOCK A LOS PRODUCTOS
                            $producto = App\Models\Producto::find($producto2["idProducto"]);
                            $producto->Stock = intval($producto->Stock) - intval($pedidoDetalle->Cantidad);
                            $producto->save();
                        }

                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    } else {
                        $payload = json_encode(array("mensaje" => "No hay productos en el pedido."));
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    $response->getBody()->write('No se encontro el usuario o esta ocupado.');
                    return $response->withHeader('Content-Type', 'application/json');
                }
            } else {
                $response->getBody()->write('No se encontro la mesa.');
                return $response->withHeader('Content-Type', 'application/json');
            }
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
            $id = $args['IdPedido'];

            $pedido = App\Models\Pedido::find($id);
            $listaDetalles = App\Models\PedidoDetalle::all();
            if ($pedido != null && count($listaDetalles) > 0) {
                $pedido->delete();

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Baja),
                        "mensaje" => "Pedido cancelado con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );

                foreach ($listaDetalles as $detalle) {
                    if ($detalle->IdPedido == $pedido->IdPedido) {
                        $detalle->delete();
                    }
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $payload = json_encode(array("mensaje" => "Error al eliminar"));
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function ModificarUno($request, $response, $args)
    {
        try {
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $body = json_decode($response->getBody());
            $header = $request->getHeaderLine('Authorization');

            $id = $args['IdPedido'];
            $body = json_decode(file_get_contents("php://input"), true);
            $estado = $body['Estado'];
            $pedido = Pedido::find($id);
            $listaDetalles = PedidoDetalle::all();

            if ($pedido != null && count($listaDetalles) > 0) {
                $pedido->Estado = $estado;
                if ($estado == 'Cancelado') {
                    $pedido->delete();
                    foreach ($listaDetalles as $detalle) {
                        if ($detalle->IdPedido == $pedido->IdPedido) {
                            $detalle->delete();
                        }
                    }
                } else if ($estado == 'Entregado') {
                    $pedido->HoraFin = date('h:i:s');
                    foreach ($listaDetalles as $detalle) {
                        if ($detalle->IdPedido == $pedido->IdPedido) {
                            $detalle->Estado = 'Entregado';
                            $detalle->save();
                        }
                    }
                }
                $pedido->save();

                $payload = json_encode(
                    array(
                        "IdUsuario" => strval($idUsuarioLogeado),
                        "IdRefUsuario" => $pedido->IdUsuario,
                        "IdAccion" =>  strval(AuditoriaAcciones::Modificacion),
                        "mensaje" => "Pedido creado con éxito",
                        "IdPedido" => $pedido->IdPedido,
                        "IdPedidoDetalle" => null,
                        "IdMesa" => $pedido->IdMesa,
                        "IdProducto" => null,
                        "IdArea" => null,
                        "Hora" => date('h:i:s')
                    )
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $payload = json_encode(array("mensaje" => "Error al eliminar"));
            }
        } catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::all();
        $payload = json_encode(array("listaPedido" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['IdPedido'];

        $pedido = Pedido::find($id);

        if ($pedido != null) {
            $payload = json_encode($pedido);
        } else {
            $payload = json_encode(array("mensaje" => "Pedido no encontrado."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
