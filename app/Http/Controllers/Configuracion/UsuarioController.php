<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {

        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi

            switch ($request->item0) {
                case '1' : $usuarios = Usuario::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);  Log::info('Entrooooo 1');
                        break;
                case '2' : $usuarios = Usuario::where('apellido','like', '%'.$request->datobuscar.'%')->paginate(10);  Log::info('Entrooooo 2');
                        break;
                case '3' : $usuarios = Usuario::where('telefono', 'like','%'.$request->datobuscar.'%')->paginate(10);  Log::info('Entrooooo 3');
                        break;
            }

        }else{
            $usuarios = Usuario::paginate(10);
        }

        return response()->json($usuarios);
    }

    public function store(Request $request){

        $rules    = [
            'usuario.nombre'    => 'required',
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

    }

    public function catalogos()
    {
        $roles = Role::all();
        $empresas = Empresa::all();

        $data = [
            "roles" => $roles,
            "empresas" => $empresas
        ];

        return response()->json($data);
    }
}
