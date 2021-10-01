<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    public function permisos() {
        return $this->belongsToMany(Permiso::class, 'modules_permisos', 'moduloid', 'permisoid');
    }
}
