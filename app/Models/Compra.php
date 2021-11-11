<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    public function usuariocreacion(){
        return $this->hasOne(Usuario::class, 'id','usuarioid');
    }

    public function estado(){
        return $this->hasOne(EstadoCompra::class, 'id','estadocompraid');
    }

    public function detalle(){
        return $this->hasMany(DetalleCompra::class,'compraid','id');
    }
}
