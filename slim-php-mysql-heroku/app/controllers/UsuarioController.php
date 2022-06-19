<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

use App\Models\AuditoriaAcciones;
use \App\Models\Usuario as Usuario;

class UsuarioController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        try{
           
            $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
            $header = $request->getHeaderLine('Authorization');
            $data = $request->getParsedBody();
      
            if(Usuario::ExisteUsuario($data['usuarioPostman'])) { 
                throw new Exception("Nombre de Usuario ya existe"); 
            }
            $idUsuarioTipo = $data['idUsuarioTipo'];  
            $idArea = $data['idArea'];
            $usuario = $data['usuarioPostman'];
            $clave = $data['clavePostman'];
            $nombre = $data['nombre'];
            $apellido = $data['apellido'];  
            $estado = 'Activo';
            $puesto = $data['puesto'];

            $usr = new Usuario();
            $usr->IdUsuarioTipo = $idUsuarioTipo;
            $usr->IdArea = $idArea;
            $usr->Usuario = $usuario;
            $usr->Nombre = $nombre;
            $usr->Apellido = $apellido;
            $usr->Estado = $estado;
            $usr->Puesto = $puesto;
            $usr->Clave = $clave;
            $usr->save();

            $payload = json_encode(
            array(
                "IdUsuario" => strval($idUsuarioLogeado),
                "IdRefUsuario" => strval($usr->IdUsuario),
                "IdAccion" =>  strval(AuditoriaAcciones::Alta),
                "mensaje" => "Usuario creado con éxito",
                "IdPedido" => null,
                "IdProducto" => null,
                "Exito" => 1, 
                "IdPedidoDetalle" => null, 
                "IdMesa" => null, 
                "IdProducto" => null, 
                "IdArea" => null,
                "Hora" => date('h:i:s'))
            );
            
            $response->getBody()->write($payload);
            return $response
            ->withHeader('Content-Type', 'application/json');
          }catch (Exception $e) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(array('error' => $e->getMessage())));
            return $response->withHeader('Content-Type', 'application/json');
          }
    }

    public function BorrarUno($request, $response, $args)
    {
        $id = $args['IdUsuario'];
        $usuario = Usuario::find($id);
        $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
        $body = json_decode($response->getBody());
        $header = $request->getHeaderLine('Authorization');
        if ($usuario != null)
        {   
         $payload = json_encode(
            array(
                "IdUsuario" => strval($idUsuarioLogeado),
                "IdRefUsuario" => strval($usuario->IdUsuario),
                "IdAccion" =>  strval(AuditoriaAcciones::Baja),
                "mensaje" => "Usuario eliminado con éxito",
                "IdPedido" => null,
                "Exito" => 1, 
                "IdPedidoDetalle" => null, 
                "IdMesa" => null, 
                "IdProducto" => null, 
                "IdArea" => $usuario->IdArea,
                "Hora" => date('h:i:s'))
            );
            $usuario->delete();
        }
        else
        {
            $payload = json_encode(array("mensaje" => "Usuario no encontrado.")); 
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $idUsuarioLogeado = AutentificadorJWT::GetUsuarioLogeado($request)->IdUsuario;
        
        $id = $args['IdUsuario'];

        $usuario = App\Models\Usuario::where('IdUsuario', '=', $id)->first();
        
        $body = json_decode(file_get_contents("php://input"), true);


    if ($usuario != null)
    {
        $user = $body['usuario'];
        $clave = $body['clave'];
        $nombre = $body['nombre'];
        $apellido = $body['apellido'];
        $estado = $body['estado'];
        $puesto = $body['puesto'];
        $idUsuarioTipo = $body['idUsuarioTipo'];  
        $idArea = $body['idArea'];
        
        $usuario->Usuario = $user;
        $usuario->Clave = $clave;
        $usuario->Nombre = $nombre;
        $usuario->Apellido = $apellido;
        $usuario->Estado = $estado;
        $usuario->Puesto = $puesto;
        $usuario->IdUsuarioTipo = $idUsuarioTipo;
        $usuario->IdArea = $idArea;
        $usuario->save();
        $payload = json_encode(
            array(
                "IdUsuario" => strval($idUsuarioLogeado),
                "IdRefUsuario" => strval($id),
                "IdAccion" =>  strval(AuditoriaAcciones::Modificacion),
                "mensaje" => "Usuario modificado con éxito",
                "IdPedido" => null,
                "Exito" => 1, 
                "IdPedidoDetalle" => null, 
                "IdMesa" => null, 
                "IdProducto" => null, 
                "IdArea" => $idArea,
                "Hora" => date('h:i:s'))
            );

        
        $response->getBody()->write($payload);
        return $response
        ->withHeader('Content-Type', 'application/json');
    }
    else
    {
        $payload = json_encode(array("mensaje" => "Usuario no modificado")); 
    }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');    
    }

    public function TraerTodos($request, $response, $args)
    {
        $listaUsuarios = App\Models\Usuario::all();
        $payload = json_encode(array("listaUsuarios" => $listaUsuarios));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    

    public function TraerUno($request, $response, $args)
    {
        $id = $args['IdUsuario'];

        $listaUsuarios = Usuario::all();
        $usuario = $listaUsuarios->find($id);

        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
