<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linea extends Model
{
    public function marca(){
        return $this->hasOne(Marca::class, 'id','marcaid');
    }
}
