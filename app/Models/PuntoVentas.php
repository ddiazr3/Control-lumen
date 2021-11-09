<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuntoVentas extends Model
{
    public function empresa()
    {
        return $this->hasOne(Empresa::class, 'id','empresaid');
    }
}
