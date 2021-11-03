<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    public function proveedor() {
        return $this->hasOne(Proveedor::class, 'id', 'proveedorid');
    }

    public function categoria() {
        return $this->hasOne(Categoria::class, 'id', 'categoriaid');
    }

    public function marca() {
        return $this->hasOne(Marca::class, 'id', 'marcaid');
    }

    public function linea() {
        return $this->hasOne(Linea::class, 'id', 'lineaid');
    }

    public function precio() {
        return $this->hasOne(PrecioBodega::class, 'productoid', 'id');
    }

    public function stock() {
        return $this->hasOne(StockBodega::class, 'productoid', 'id');
    }
}
