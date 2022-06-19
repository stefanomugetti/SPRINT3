<?php

require_once './models/Producto.php';

use \App\Models\Producto as Producto;

class FilesManagement
{
                        //nombre - precio - tiempoMinutos - area - tipo - stock
    public static function ProductosToCSV(string $path, $arrayObjetos)
    {
        $escrituraSalioBien = true;
        if ($path != null && $arrayObjetos != null) {
            $archivo = fopen($path, "w");
            foreach ($arrayObjetos as $objeto) {
                if (!is_null($objeto)) {
                    $string =   $objeto->Nombre . "," .
                        $objeto->TiempoEspera . "," .
                        $objeto->Area . "," .
                        $objeto->PrecioUnidad . "," .
                        $objeto->TipoProducto . "," .
                        $objeto->Stock;

                    fwrite($archivo, $string . PHP_EOL);
                } else {
                    return false;
                }
            }
            fclose($archivo);
        } else {
            return false;
        }

        return $escrituraSalioBien;
    }
    /*IDUSUARIOBUSCADO ES OPCIONAL, SI NO SE LO PASA NO SERA TOMADO EN CUENTA Y NO FILTRARA POR ID */
    public static function AuditoriaToCSV(string $path, $arrayObjetos,$idUsuarioBuscado = -1)
    {
        $escrituraSalioBien = true;
        if ($path != null && $arrayObjetos != null) {
            $archivo = fopen($path, "w");
            foreach ($arrayObjetos as $objeto) {
                if (!is_null($objeto)) {
                    if(strval($objeto->IdUsuario) == strval($idUsuarioBuscado) || $idUsuarioBuscado == -1){
                    if(!isset($objeto->IdRefUsuario)){
                        $IdRefUsuario = 0;
                    }
                    if(!isset($objeto->IdMesa)){
                        $IdMesa = 0;
                    }
                    if(!isset($objeto->IdPedido)){
                        $IdPedido = 0;
                    }
                    if(!isset($objeto->IdProducto)){
                        $IdProducto = 0;
                    }
                    $string =   strval($objeto->IdUsuario) . "," .
                        $IdRefUsuario . "," .
                        $objeto->Hora . "," .
                        $objeto->IdAccion . "," .
                        $IdMesa . "," .
                        $IdPedido . "," .
                        $IdProducto ;

                    fwrite($archivo, $string . PHP_EOL);
                }
                } else {
                    return false;
                }
            }
            fclose($archivo);
        } else {
            return false;
        }

        return $escrituraSalioBien;
    }
    //              nombre - precio - tiempoMinutos - area - tipo - stock

    public static function LeerProductosCSV(string $path)
    {
        $list = array();
        $archivo = fopen($path, "r");
        $archivoLength = filesize($path);

        $i = 0;
        while (!feof($archivo)) {
            if ($archivoLength < 2) {
                break;
            }
            $stringLineaLeida = fgets($archivo, $archivoLength);
            if (strlen($stringLineaLeida) > 1) {
                $array = explode(',', $stringLineaLeida);

                $objetoAuxiliar = new Producto();
                $objetoAuxiliar->Nombre = $array[0];
                $objetoAuxiliar->Stock = $array[1];
                $objetoAuxiliar->PrecioUnidad = $array[2];
                $objetoAuxiliar->TiempoEspera = $array[3];
                $objetoAuxiliar->Area = $array[4];
                $tipoProducto = explode(PHP_EOL, $array[5]); //SACO SALTO DE LINEA
                $objetoAuxiliar->TipoProducto = $tipoProducto[0];

                array_push($list, $objetoAuxiliar);
            }
            $i++;
        }
        fclose($archivo);

        if (count($list) > 0) {
            foreach ($list as $producto) {
                $prodAux = Producto::where('Nombre', '=', $producto->Nombre)->first();
                if ($prodAux == null) {
                    $producto->save();
                } else {
                    $prodAux->Stock = intval($producto->Stock) + intval($prodAux->Stock);
                    $prodAux->PrecioUnidad = $producto->PrecioUnidad;
                    $prodAux->TiempoEspera = $producto->TiempoEspera;
                    $prodAux->Area = $producto->Area;
                    $prodAux->TipoProducto = $producto->TipoProducto;
                    $prodAux->update();
                }
            }
        }
        return $list;
    }
}
