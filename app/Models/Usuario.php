<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    /**
        El modelo tiene que ir en singular y migracion en prural
     */
    use HasFactory, Authenticatable, Authorizable;

    protected $guarded = ["id"];
    protected $table = "usuario";

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function isGod()
    {
        $rolesIds = $this->roleIds();
        return (in_array(1, $rolesIds));

    }

    public function roles()
    {
		return $this->belongsToMany(Role::class, 'roles_usuarios','usuarioid','roleid');
    }

	public function roleIds()
    {
		return $this->roles->pluck('id')->toArray();
	}

    public function empresa()
    {
		return $this->hasOne(Empresa::class, 'id','empresaid');
    }

    //sirve para que retorne los permisos del modulo que el usuario esta accediendo
    /*
     * Ejemplo si entra al index del controller usuario entonces le devolvera solo los permisos que tiene para hacer en esa pantalla
     * ***/
    public static function permisosUsuarioLogeado($aTo){

        $userLoged = Usuario::with([
            'roles' => function ($query){
                $query->with('role_module_permisos');
            }
        ])->find(Auth::id());


        $moduloLogued = Modulo::where('to',$aTo)->first();
        $validateMP= [];

        foreach ($userLoged->roles as $u) {
            $roleModulePermiso = ModuloPermiso::
                select('moduloid')
                ->whereIn('id',$u->modules_permisos_ids())
                ->where('moduloid', $moduloLogued->id)
                ->orderBy('moduloid')
                ->groupBy('moduloid')
                ->get();

            foreach ($roleModulePermiso as $rmp){
                    $roleModulePermisoI = ModuloPermiso::whereIn('id',$u->modules_permisos_ids())
                        ->where('moduloid',$rmp->moduloid)
                        ->orderBy('permisoid')
                        ->pluck('permisoid')
                        ->toArray();
                    $permisos = Permiso::whereIn('id',$roleModulePermisoI)
                        ->pluck('nombre')
                        ->toArray();
                    array_push($validateMP, $permisos);
            }
        }
        return $validateMP;
    }

}
