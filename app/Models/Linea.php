<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linea extends Model
{
    protected $fillable = ["nombre", "empresaid", "marcaid"];

    public function marca(){
        return $this->hasOne(Marca::class, 'id','marcaid');
    }
}
