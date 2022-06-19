<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditoriaAcciones extends Model
{
    // Identificadores de acciones
    const Login = 1;
    const Alta = 2;
    const Baja = 3;
    const Modificacion = 4;
    const CargaDatos = 20;
    const DescargaDatos = 21;

    use SoftDeletes; 

    protected $table = 'acciones';
    protected $primaryKey = 'IdAccion';
    public $incrementing = true;

    // const CREATED_AT = 'FechaAlta';
    // const DELETED_AT = 'FechaBaja';
    // const UPDATED_AT = 'FechaModificacion';

    protected $fillable = [
         'Tipo'//,
        // 'fechaAlta', 'fechaModificacion', 'fechaBaja'
    ];
    
    public function listUsuarioAccion()
    {
        return $this->hasMany(UsuarioAccion::class, 'idUsuarioAccionTipo');
    }
}

class UsuarioTipo extends Model
{
    // Identificadores
    const Administrador = 5;
    const Socio = 6;
    const Mozo = 7;
    const Bartender = 8;
    const Bartender_Cerveza = 9;
    const Cocinero = 10;

    use SoftDeletes; 

    protected $table = 'usuarioTipo';
    protected $primaryKey = 'IdUsuarioTipo';
    public $incrementing = true;
    // public $timestamps = false;

    // const CREATED_AT = 'FechaAlta';
    // const DELETED_AT = 'FechaBaja';
    // const UPDATED_AT = 'FechaModificacion';

    protected $fillable = [
        'Tipo',
    ];

    public function listUsuario()
    {
        return $this->hasMany(Usuario::class, 'IdUsuarioTipo');
    }
}
    class Area extends Model
    {
        // Identificadores
        const Administracion = 11;
        const Salon = 12;
        const Barra_Vinos = 13;
        const Barra_Cerveza = 14;
        const Cocina = 15;
        const Candy_Bar = 16;
    
        use SoftDeletes; // delete de forma lógica
    
        protected $table = 'area';
        protected $primaryKey = 'IdArea';
        public $incrementing = true;
        // public $timestamps = false;
    
        // const CREATED_AT = 'fechaAlta';
        // const DELETED_AT = 'fechaBaja';
        // const UPDATED_AT = 'fechaModificacion';
    
        protected $fillable = [
            'descripcion'
            // 'fechaAlta', 'fechaModificacion', 'fechaBaja'
        ];
    
        public function listUsuario()
        {
            return $this->hasMany(Usuario::class, 'IdArea');
        }
    
        public function listProducto()
        {
            return $this->hasMany(Producto::class, 'IdArea');
        }
    }
