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

    public function roles()
    {
		return $this->belongsToMany(Role::class, 'roles_usuarios');
    }

	public function roleIds()
    {
		return $this->roles->pluck('id')->toArray();
	}

    public function empresa()
    {
		return $this->hasOne(Empresa::class, 'id','empresaid');
    }

}
