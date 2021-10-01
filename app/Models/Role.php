<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = ['id'];

    public function role_module_permisos()
    {
        return $this->hasMany(RolModuloPermiso::class, 'roleid', 'id');
    }

    public function modules_permisos_ids()
    {
        return $this->role_module_permisos->pluck('modulepermisoid')->toArray();
    }

    public function empresa(){
        return $this->hasOne(Empresa::class, 'id','empresaid');
    }

}
