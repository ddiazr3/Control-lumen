<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuloPermiso extends Model
{
    protected $table = 'modules_permisos';

    /*public function modulos()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function permisos()
    {
        return $this->belongsTo(Permiso::class);
    }*/
    public function permiso()
    {
        return $this->hasOne(Permiso::class, 'id','permisoid');
    }

    public function rolmodulopermisos(){
        return $this->belongsToMany(Role::class, 'rol_modules_permisos', 'modulepermisoid', 'roleid');
    }
}
