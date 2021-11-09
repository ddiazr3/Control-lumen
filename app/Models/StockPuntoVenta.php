<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPuntoVenta extends Model
{
    protected $table = 'stock_punto_ventas';

    public function producto() {
        return $this->hasOne(Producto::class, 'id', 'productoid');
    }
}
