<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
    public static function permisosUsuarioLogeado($userLoged,$aTo){

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
