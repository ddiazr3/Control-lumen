<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    public function stockbodega() {
        return $this->hasMany(StockBodega::class, 'bodegaid', 'id');
    }

    public function preciobodega() {
        return $this->hasMany(PrecioBodega::class, 'bodegaid', 'id');
    }
}
