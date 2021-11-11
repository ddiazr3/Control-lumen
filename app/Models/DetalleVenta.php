<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    public function producto(){
        return $this->hasOne(Producto::class, 'id','productoid');
    }
}
