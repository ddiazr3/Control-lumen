<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    public function detalle(){
        return $this->hasMany(DetalleVenta::class,'ventaid','id');
    }

    public function estado(){
        return $this->hasOne(EstadoVenta::class, 'id','estadoventaid');
    }

    public function usuariocreacion(){
        return $this->hasOne(Usuario::class, 'id','usuarioid');
    }

    public function puntoventa(){
        return $this->hasOne(PuntoVentas::class, 'id','puntoventaid');
    }
}
