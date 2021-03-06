<?php

namespace App\Http\Controllers\Configuracion;

use App\Exports\UsuarioExport;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Modulo;
use App\Models\ModuloPermiso;
use App\Models\Role;
use App\Models\RoleUsuarios;
use App\Models\RolModuloPermiso;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{
    private $path = '/configuracion/roles';
    public function index(Request $request)
    {

        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "roles" => [],
                "permisos" => []
            ];
            return response()->json($data);
        }


        if (isset($request->search))
        {
            // 1 nombre
            switch ($request->item0) {
                case '1' :
                    if(Auth::user()->isGod()){
                        $roles = Role::with('empresa')->where('id','<>', 1)->where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $roles = Role::with('empresa')->where('id','<>', 1)->where('empresaid',Auth::user()->empresaid)->where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }

                    break;
                case '2' :

                    if(Auth::user()->isGod()){
                        $roles = Role::with(['empresa' => function($query) use ($request){
                            $query->where('nombre','like','%'.$request->datobuscar.'%');
                        }])->where('empresaid',Auth::user()->empresaid)->paginate(10);
                    }else{
                        $roles = Role::with(['empresa' => function($query) use ($request){
                            $query->where('nombre','like','%'.$request->datobuscar.'%');
                        }])->where('empresaid',Auth::user()->empresaid)->paginate(10);
                    }
                    break;
            }

        }else{
            if(Auth::user()->isGod()){
                $roles = Role::with('empresa')->where('id','<>', 1)->paginate(10);
            }else{
                $roles = Role::with('empresa')->where('empresaid',Auth::user()->empresaid)->where('id','<>', 1)->paginate(10);
            }

        }

        foreach ($roles as $r){
            $r->idcrypt = Crypt::encrypt($r->id);
        }
        $data = [
            "roles" => $roles,
            "permisos" => $permisos
        ];
        return response()->json($data);
    }

    public function store(Request $request){

        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('create',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }

        $rules    = [
            'role.nombre'    => 'required',
            'role.descripcion'  => 'required',
            'role.permisosIds'   => 'required|min:1'
        ];

        $messages = [
            'role.nombre.required'        => 'El nombre es requerido',
            'role.descripcion.required'   => 'La descripcion es requerido',
            'role.permisosIds.required'   => 'Permisos requeridos',
            'role.permisosIds.min'        => 'Al menos debe seleccionar un permiso'
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->role['idcrypt']) and $request->role['idcrypt']){
            $role = Role::find(Crypt::decrypt($request->role['idcrypt']));
            $roleModelPermisoDeleted = RolModuloPermiso::where('roleid', $role->id)->get();
            foreach ($roleModelPermisoDeleted as $rd){
                $rd->delete();
            }
        }else{
            $role = new Role();
        }
        $role->nombre = $request->role['nombre'];
        $role->descripcion = $request->role['descripcion'];
        $role->empresaid = Auth::user()->isGod() ? $request->role['empresaid'] : Auth::user()->empresaid;
        $role->usuariocreacionid =  isset($request->role['usuariocreacionid']) ? Crypt::decrypt($request->role['usuariocreacionid']) : null;
        $role->save();
        //agregandoRol
        $permisos = $request->role['permisosIds'];
        foreach ($permisos as $per){
            $roleModelPermiso = new RolModuloPermiso();
            $roleModelPermiso->modulepermisoid = $per;
            $roleModelPermiso->roleid = $role->id;
            $roleModelPermiso->save();
        }
        return response()->json(200);
    }

    public function edit($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('edit',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $id = Crypt::decrypt($id);
        $role = Role::find($id);
        $role->idcrypt = Crypt::encrypt($id);
        $role->permisosIds = $role->modules_permisos_ids();
        return response()->json($role);
    }

    public function catalogos(Request $request)
    {
        //este query cabal me sirve para le menu
       // $modulosPermisos = Modulo::with('permisos')->whereNotNull('to' )->get();

        $modulos = Modulo::whereNotNull('to' )->get();

        $moduloPermisoInstance = [];
        $moduloInstance = [];
        foreach ($modulos as $m){
            $mp = ModuloPermiso::with('permiso')->where('moduloid', $m->id)->get();

            foreach ($mp as $mpi){
                $datoModuloPermiso = [
                    "id" => $mpi->id,
                    "nombrefriendly" => $mpi->permiso->nombre
                ];
                array_push($moduloPermisoInstance,$datoModuloPermiso);
            }

            $datosModulo = [
                "id" => $m->id,
                "nombre" => $m->nombre,
                "permisos" => $moduloPermisoInstance
            ];
            array_push($moduloInstance,$datosModulo);
            $moduloPermisoInstance = [];
        }

        if(Auth::user()->isGod()){
            $empresas = Empresa::all();
        }else{
            $empresas = Empresa::where('id',Auth::user()->empresaid)->get();
        }


        $data = [
            "modulosPermisos" => $moduloInstance,
            "empresas" => $empresas
        ];

        return response()->json($data);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('destroy',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $role = Role::find($id);
        $role->delete();
        return response()->json("ok");
    }

}
