<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    public function producto(){
        return $this->hasOne(Producto::class, 'id','productoid');
    }
}
