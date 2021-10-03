<?php

namespace App\Http\Controllers\Configuracion;

use App\Exports\SolicitudEntregaExport;
use App\Exports\UsuarioExport;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Modulo;
use App\Models\ModuloPermiso;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\RoleUsuarios;
use App\Models\Usuario;
use Closure as BaseClosure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi

            switch ($request->item0) {
                case '1' : $usuarios = Usuario::where('nombre','like','%'.$request->datobuscar.'%')->where('id','<>', 1)->paginate(10);  Log::info('Entrooooo 1');
                        break;
                case '2' : $usuarios = Usuario::where('apellido','like', '%'.$request->datobuscar.'%')->where('id','<>', 1)->paginate(10);  Log::info('Entrooooo 2');
                        break;
                case '3' : $usuarios = Usuario::where('telefono', 'like','%'.$request->datobuscar.'%')->where('id','<>', 1)->paginate(10);  Log::info('Entrooooo 3');
                        break;
            }

        }else{
            $usuarios = Usuario::where('id','<>', 1)->paginate(10);
        }

        foreach ($usuarios as $u){
            $u->idcrypt = Crypt::encrypt($u->id);
            $u->rolesid = $u->roleIds();
        }

        return response()->json($usuarios);
    }

    public function store(Request $request){

        $rules    = [
            'usuario.nombre'    => 'required',
            'usuario.contrasenia' => 'required',
            'usuario.apellido'  => 'required',
            'usuario.correo'    => 'required|email',
            'usuario.dpi'       => 'required|numeric',
            'usuario.telefono'  => 'required|numeric',
            'usuario.direccion' => 'required',
            'usuario.empresaid' => 'required|min:1',
            'usuario.rolesid'   => 'required|min:1'
        ];

        $messages = [
            'usuario.nombre.required'        => 'El nombre es requerido',
            'usuario.contrasenia.required'        => 'La contraseña es requerido',
            'usuario.apellido.required'      => 'El apellido es requerido',
            'usuario.correo.required'        => 'El correo es requerida',
            'usuario.dpi.required'           => 'El dpi es requerido',
            'usuario.dpi.numeric'            => 'El dpi es campo numerico',
            'usuario.telefono.required'      => 'El telefono es requerido',
            'usuario.telefono.numeric'       => 'El telefono es campo numerico',
            'usuario.direccion.required'     => 'La direccion es requerida',
            'usuario.empresaid.required'     => 'La empresa es requerida',
            'usuario.empresaid.min'          => 'Al menos debe seleccionar una empresa',
            'usuario.rolesid.required'       => 'El rol es requerido',
            'usuario.rolesid.min'            => 'Al menos debe seleccionar un rol'
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->usuario['idcrypt']) and $request->usuario['idcrypt']){
            $usuario = Usuario::find(Crypt::decrypt($request->usuario['idcrypt']));
        }else{
            $usuario = new Usuario();
            $usuario->password = Hash::make($request->usuario['contrasenia']);
        }
        $usuario->nombre = $request->usuario['nombre'];
        $usuario->apellido = $request->usuario['apellido'];
        $usuario->correo = $request->usuario['correo'];
        $usuario->dpi = $request->usuario['dpi'];
        $usuario->telefono = $request->usuario['telefono'];
        $usuario->direccion = $request->usuario['direccion'];
        $usuario->empresaid = $request->usuario['empresaid'];
        $usuario->save();

        if(isset($request->usuario['idcrypt']) and $request->usuario['idcrypt']){
            $roleUsuarioDeleted = RoleUsuarios::where('usuarioid', $usuario->id)->get();
            foreach ($roleUsuarioDeleted as $rd){
                $rd->delete();
            }
        }

        //agregandoRol
        $roles = $request->usuario['rolesid'];
        foreach ($roles as $role){
            $roleUsuario = new RoleUsuarios();
            $roleUsuario->usuarioid = $usuario->id;
            $roleUsuario->roleid = $role;
            $roleUsuario->save();
        }
        return response()->json(200);
    }

    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $usuarios = Usuario::find($id);
        $usuarios->idcrypt = Crypt::encrypt($id);
        $usuarios->rolesid = $usuarios->roleIds();
        return response()->json($usuarios);
    }

    public function catalogos()
    {
        $roles = Role::where('id','<>', 1)->get();
        $empresas = Empresa::all();

        $data = [
            "roles" => $roles,
            "empresas" => $empresas
        ];

        return response()->json($data);
    }

    public function eliminar($id)
    {

        $usuarios = Usuario::find($id);
        $usuarios->activo = false;
        $usuarios->update();
        return response()->json($usuarios);
    }

    public function activar($id)
    {
        $usuarios = Usuario::find($id);
        $usuarios->activo = true;
        $usuarios->update();
        return response()->json($usuarios);
    }

    public function exportar(Request $request){

        $usuarios = Usuario::with(['roles']);

        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi
            switch ($request->item0) {
                case '1' : $usuarios = $usuarios->where('usuario.nombre','like','%'.$request->datobuscar.'%');
                    break;
                case '2' : $usuarios = $usuarios->where('usuario.apellido','like', '%'.$request->datobuscar.'%');
                    break;
                case '3' : $usuarios = $usuarios->where('usuario.telefono', 'like','%'.$request->datobuscar.'%');
                    break;
            }
        }

        $usuarios = $usuarios->get();

        $dataExport = [];

        foreach ($usuarios as $u){
           $nameRoles = "";
            if($u->roles){
                foreach ($u->roles as $ur){
                    $nameRoles .= $ur->nombre." - ";
                }
            }
            $dataExportInstance = [
                "nombres" => $u->nombre,
                "apellido" => $u->apellido,
                "telefono"  => $u->telefono,
                "direccion" => $u->direccion,
                "roles" => $nameRoles
            ];
            array_push($dataExport, $dataExportInstance);

        }

        return Excel::download(new UsuarioExport(collect($dataExport)),'usuarios.xlsx');
    }

    //EJEMPLO DE COMO EXPORTAR PDF
    public function exportPDF(Request $request)
    {
        $nombre = $request->nombre;
        $contxt = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed'=> TRUE
            ]
        ]);
        $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        $pdf->getDomPDF()->setHttpContext($contxt);
        $pdf = PDF::loadView('pdf.test',["nombre" => $nombre]);
        return $pdf->download('test.pdf');
    }

    public function sendMessage(Request $request){
        event(new \App\Events\MessageEvent($request->message));
    }

    public function login(Request $request){

        $correo = $request->correo;
        $pass = $request->password;


        $usuario = Usuario::with([
            'roles' => function ($query){
                $query->with('role_module_permisos');
            }
        ])->where('correo', $correo)->first();

        if(!$usuario){
            return response()->json([
                'message' => "Correo invalido"
            ],401);
        }

        if(!Hash::check($pass, $usuario->password)) {
            return response()->json([
                'message' => "Contrasenia invalido"
            ],401);
        }

        $usuarioReturn = [
            "nombreCompleto" => $usuario->nombre ." ". $usuario->apellido,
            "correo" => $usuario->correo,
            "telefono" => $usuario->telefono,
            "idUsuarioCrypt" => Crypt::encrypt($usuario->id),
            "token" => $usuario->token,
            "idsRoles" => $usuario->roleIds(),
            "empresa" => [
                "nombre" => $usuario->usuario ? $usuario->empresa->nombre : null,
                "nit" => $usuario->usuario ? $usuario->empresa->nit : null,
                "idCrypt" => $usuario->usuario ? Crypt::encrypt($usuario->empresa->id) : null
            ]
        ];

        $modulosPermisos = [];
        $validateMP = [];

        foreach ($usuario->roles as $u){
            $roleModulePermiso = ModuloPermiso::whereIn('id',$u->modules_permisos_ids())->orderBy('moduloid')->get();



            $idmodulo = 0;
            $idpadre = 0;
            $arrayItems = [];
            $moduloPadre = null;
            foreach ($roleModulePermiso as $rmp){

                if($rmp->moduloid != $idmodulo){
                    $idmodulo = $rmp->moduloid;
                    $modulo = Modulo::find($idmodulo);
                   // Log::info("modulo ".$modulo);
                   // Log::info( $u->modules_permisos_ids());
                    $pInstance = self::permisosModuloI($modulo, $u->modules_permisos_ids());
                    Log::info($pInstance);
                    array_push($validateMP, $pInstance);

                    if($modulo->padreId){

                        $hijos = [];
                        if($modulo->padreId != $idpadre){
                            $idpadre = $modulo->padreId;
                            $moduloPadre = Modulo::with('hijos')->find($modulo->padreId);


                            foreach ($moduloPadre->hijos as $mp){
                                $modulo = Modulo::find($mp->id);
                                $roleModulePermisoI = ModuloPermiso::whereIn('id',$u->modules_permisos_ids())
                                    ->where('moduloid',$mp->id )
                                    ->orderBy('permisoid')
                                    ->pluck('permisoid')
                                    ->toArray();

                                if($roleModulePermisoI){
                                    $permisos = Permiso::whereIn('id',$roleModulePermisoI)
                                        ->pluck('nombre')
                                        ->toArray();

                                    $itemHijo =
                                        [
                                            "title"     => $modulo->title,
                                            "icon"      => $modulo->icon,
                                            "to"        => $modulo->to,
                                            "permisos"  => $permisos
                                        ];
                                    array_push($hijos,$itemHijo);
                                }

                            }

                            $itemPadre =
                                [
                                    "title"     => $moduloPadre->title,
                                    "icon"      => $moduloPadre->icon,
                                    "items"     => $hijos
                                ];
                            $hijos = [];
                            array_push($modulosPermisos,$itemPadre);

                        }


                   }else{

                        $roleModulePermisoI = ModuloPermiso::whereIn('id',$u->modules_permisos_ids())
                            ->where('moduloid',$rmp->id )
                            ->orderBy('permisoid')
                            ->pluck('permisoid')
                            ->toArray();
                        $permisos = Permiso::whereIn('id',$roleModulePermisoI)
                            ->pluck('nombre')
                            ->toArray();
                        $item =
                            [
                                "title"     => $modulo->title,
                                "icon"      => $modulo->icon,
                                "to"        => $modulo->to,
                                "permisos"  => $permisos
                            ];

                        array_push($modulosPermisos,$item);
                    }
                }
            }

        }

        $data = [
            "usuario" => $usuarioReturn,
            "modulos" => $modulosPermisos,
            "validarMP" => $validateMP
        ];

        return response()->json($data);

    }

    public static function permisosModuloI(Modulo $modulo, $u)
    {
        $roleModulePermisoI = ModuloPermiso::whereIn('id',$u)
            ->where('moduloid',$modulo->id )
            ->orderBy('permisoid')
            ->pluck('permisoid')
            ->toArray();
        $permisos = Permiso::whereIn('id',$roleModulePermisoI)
            ->pluck('nombre')
            ->toArray();

        $item =
            [
                "to"        => $modulo->to,
                "permisos"  => $permisos
            ];



        return $item;

    }
}
