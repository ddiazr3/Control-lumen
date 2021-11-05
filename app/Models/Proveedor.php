<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $fillable = ["nombre", "empresaid", "direccion", "telefono"];
    protected $table = 'proveedores';
}
