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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{
    public function index(Request $request)
    {

        if(!isset($request->id)){
            return response()->json([
                'message' => "a ocurrido un error comunicarse con su administrador"
            ], 404);
        }

        $userLoged = Usuario::with([
            'roles' => function ($query){
                $query->with('role_module_permisos');
            }
        ])->find(Crypt::decrypt($request->id));

        $permisos= Usuario::permisosUsuarioLogeado($userLoged,'/configuracion/roles');

        $isGod = false;
        if(in_array(1, $userLoged->roleIds())){
            $isGod = true;
        }

        if (isset($request->search))
        {
            // 1 nombre
            switch ($request->item0) {
                case '1' :
                    if($isGod){
                        $roles = Role::with('empresa')->where('id','<>', 1)->where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $roles = Role::with('empresa')->where('id','<>', 1)->where('empresaid',$userLoged->empresaid)->where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }

                    break;
                case '2' :

                    if($isGod){
                        $roles = Role::with(['empresa' => function($query) use ($request){
                            $query->where('nombre','like','%'.$request->datobuscar.'%');
                        }])->where('empresaid',$userLoged->empresaid)->paginate(10);
                    }else{
                        $roles = Role::with(['empresa' => function($query) use ($request){
                            $query->where('nombre','like','%'.$request->datobuscar.'%');
                        }])->where('empresaid',$userLoged->empresaid)->paginate(10);
                    }
                    break;
            }

        }else{
            if($isGod){
                $roles = Role::with('empresa')->where('id','<>', 1)->paginate(10);
            }else{
                $roles = Role::with('empresa')->where('empresaid',$userLoged->empresaid)->where('id','<>', 1)->paginate(10);
            }

        }

        foreach ($roles as $r){
            $r->idcrypt = Crypt::encrypt($r->id);
            $r->permisos = $permisos;
        }

        return response()->json($roles);
    }

    public function store(Request $request){

        Log::info($request);

        $rules    = [
            'role.nombre'    => 'required',
            'role.descripcion'  => 'required',
            'role.empresaid' => 'required|min:1',
            'role.permisosIds'   => 'required|min:1'
        ];

        $messages = [
            'role.nombre.required'        => 'El nombre es requerido',
            'role.descripcion.required'   => 'La descripcion es requerido',
            'role.empresaid.required'     => 'La empresa es requerida',
            'role.empresaid.min'          => 'Al menos debe seleccionar una empresa',
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
        $role->empresaid = $request->role['empresaid'];
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

        if(!isset($request->id)){
            return response()->json([
                'message' => "a ocurrido un error comunicarse con su administrador"
            ], 404);
        }

        $userLoged = Usuario::find(Crypt::decrypt($request->id));

        $isGod = false;
        if(in_array(1, $userLoged->roleIds())){
            $isGod = true;
        }

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

        if($isGod){
            $empresas = Empresa::all();
        }else{
            $empresas = Empresa::where('id',$userLoged->empresaid)->get();
        }


        $data = [
            "modulosPermisos" => $moduloInstance,
            "empresas" => $empresas
        ];

        return response()->json($data);
    }

    public function eliminar($id)
    {

        $role = Role::find($id);
        $role->delete();
        return response()->json("ok");
    }

}
