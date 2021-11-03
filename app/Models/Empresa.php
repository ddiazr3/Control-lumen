<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';

    public function puntoventas() {
        return $this->hasMany(PuntoVentas::class, 'empresaid', 'id');
    }

    public function bodega() {
        return $this->hasOne(Bodega::class, 'empresaid', 'id');
    }
}
