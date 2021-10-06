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
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{

    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado('/configuracion/usuarios');

        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi
            switch ($request->item0) {
                case '1' :

                    if(Auth::user()->isGod()){
                        $usuarios = Usuario::where('nombre','like','%'.$request->datobuscar.'%')->where('id','<>', 1)->paginate(10);
                    }else{
                        $usuarios = Usuario::where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',$userLoged->empresaid )->where('id','<>', 1)->paginate(10);
                    }

                        break;
                case '2' :

                    if(Auth::user()->isGod()){
                        $usuarios = Usuario::where('apellido','like','%'.$request->datobuscar.'%')->where('id','<>', 1)->paginate(10);
                    }else{
                        $usuarios = Usuario::where('apellido','like','%'.$request->datobuscar.'%')->where('empresaid',$userLoged->empresaid )->where('id','<>', 1)->paginate(10);
                    }
                        break;
                case '3' :

                    if(Auth::user()->isGod()){
                        $usuarios = Usuario::where('telefono','like','%'.$request->datobuscar.'%')->where('id','<>', 1)->paginate(10);
                    }else{
                        $usuarios = Usuario::where('telefono','like','%'.$request->datobuscar.'%')->where('empresaid',$userLoged->empresaid )->where('id','<>', 1)->paginate(10);
                    }
                     break;
            }

        }else{
            if(Auth::user()->isGod()){
                $usuarios = Usuario::where('id','<>', 1)->paginate(10);
            }else{
                $usuarios = Usuario::where('id','<>', 1)->where('empresaid',$userLoged->empresaid )->paginate(10);
            }

        }

        foreach ($usuarios as $u){
            $u->idcrypt = Crypt::encrypt($u->id);
            $u->rolesid = $u->roleIds();
            $u->permisos = $permisos;
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
        $usuario->usuariocreacionid =  isset($request->usuario['usuariocreacionid']) ? Crypt::decrypt($request->usuario['usuariocreacionid']) : null;
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

    public function catalogos(Request $request)
    {

        if(!isset($request->id)){
            return response()->json([
                'message' => "a ocurrido un error comunicarse con su administrador"
            ], 404);
        }

        $userLoged = Usuario::find(Crypt::decrypt($request->id));

        if(Auth::user()->isGod()){
            $roles = Role::where('id','<>', 1)->get();
            $empresas = Empresa::all();
        }else{
            Log::info("usuariempresa catalogo $userLoged->empresaid ");
            $roles = Role::where('id','<>', 1)->where('empresaid',$userLoged->empresaid)->get();
            $empresas = Empresa::where('id',$userLoged->empresaid)->get();
        }


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
        $credentials = $request->only(['correo', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info($token);

        $tok = $this->respondWithToken($token);

        $usuarioReturn = [
            "nombreCompleto" => $usuario->nombre ." ". $usuario->apellido,
            "correo" => $usuario->correo,
            "telefono" => $usuario->telefono,
            "idUsuarioCrypt" => Crypt::encrypt($usuario->id),
            "token" => $tok,
            "idsRoles" => $usuario->roleIds(),
            "empresa" => [
                "nombre" => $usuario->usuario ? $usuario->empresa->nombre : null,
                "nit" => $usuario->usuario ? $usuario->empresa->nit : null,
                "idCrypt" => $usuario->usuario ? Crypt::encrypt($usuario->empresa->id) : null
            ]
        ];

        $modulosPermisos = [];

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
                    if($modulo->padreId){

                        $hijos = [];
                        if($modulo->padreId != $idpadre){
                            $idpadre = $modulo->padreId;
                            $moduloPadre = Modulo::with('hijos')->find($modulo->padreId);


                            foreach ($moduloPadre->hijos as $mp){
                                $modulo = Modulo::find($mp->id);

                                $itemHijo =
                                    [
                                        "title"     => $modulo->title,
                                        "icon"      => $modulo->icon,
                                        "to"        => $modulo->to
                                    ];
                                array_push($hijos,$itemHijo);

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
                        $item =
                            [
                                "title"     => $modulo->title,
                                "icon"      => $modulo->icon,
                                "to"        => $modulo->to
                            ];

                        array_push($modulosPermisos,$item);
                    }
                }
            }

        }

        $data = [
            "usuario" => $usuarioReturn,
            "modulos" => $modulosPermisos
        ];

        return response()->json($data);

    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ], 200);
    }

    public function logout(){
        \auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

}
