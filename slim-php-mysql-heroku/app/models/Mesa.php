<?php

    namespace app\models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    
    class Mesa extends Model
    {
        use SoftDeletes;
    
        protected $primaryKey = 'IdMesa';
        protected $table = 'mesas';
        public $incremeting = true;
        public $timestamps = true;
    
        const CREATED_AT = 'fechaAlta';
        const DELETED_AT = 'fechaBaja';
        const UPDATED_AT = 'fechaModificacion';
    
        public $fillable = [
            'Estado','Descripcion',
            'Codigo','FechaAlta',
            'FechaBaja','FechaModificacion'
        ]; 
    }
?>