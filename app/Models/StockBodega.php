<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBodega extends Model
{
    protected $table = 'stock_bodegas';

    public function producto() {
        return $this->hasOne(Producto::class, 'id', 'productoid');
    }
}
