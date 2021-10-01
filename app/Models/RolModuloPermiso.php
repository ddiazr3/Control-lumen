<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolModuloPermiso extends Model
{
    protected $table = 'rol_modules_permisos';

    public function modulo_permisos()
    {
        return $this->belongsTo(ModuloPermiso::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
