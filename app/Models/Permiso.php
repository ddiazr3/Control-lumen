<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    public function modulos() {
        return $this->belongsToMany(Permiso::class, 'modules_permisos', 'permisoid', 'moduloid');
    }
}
