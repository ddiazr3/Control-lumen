<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    /**
        El modelo tiene que ir en singular y migracion en prural
     */
    use HasFactory;

    protected $guarded = ["id"];
    protected $table = "usuario";
}
