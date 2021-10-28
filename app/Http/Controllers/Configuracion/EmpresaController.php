<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmpresaController extends Controller
{
    private $path = '/configuracion/empresas';
    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "empresas" => [],
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
                        $empresas = Empresa::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $empresas = Empresa::where('nombre','like','%'.$request->datobuscar.'%')->where('id',Auth::user()->empresaid)->paginate(10);
                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $empresas = Empresa::paginate(10);
            }else{
                $empresas = Empresa::where('id',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($empresas as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }

        $data = [
            "empresas" => $empresas,
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
            'empresa.nombre'    => 'required',
            'empresa.direccion' => 'required',
            'empresa.nit'       => 'required|numeric',
            'empresa.telefono'  => 'required|numeric'
        ];

        $messages = [
            'empresa.nombre.required'        => 'El nombre es requerido',
            'empresa.direccion.required'     => 'La direccion es requerido',
            'empresa.nit.required'           => 'El nit es requerido',
            'empresa.nit.numeric'            => 'El nit es campo numerico',
            'empresa.telefono.required'      => 'El telefono es requerido',
            'empresa.telefono.numeric'       => 'El telefono es campo numerico'
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->empresa['idcrypt']) and $request->empresa['idcrypt']){
            $empr = Empresa::find(Crypt::decrypt($request->empresa['idcrypt']));
        }else{
            $empr = new Empresa();
        }
        $empr->nombre = $request->empresa['nombre'];
        $empr->direccion = $request->empresa['direccion'];
        $empr->nit = $request->empresa['nit'];
        $empr->telefono = $request->empresa['telefono'];
        $empr->usuariocreacionid = Auth::user()->empresaid;
        $empr->logo = isset($request->empresa['logo']) ? $request->empresa['logo'] : null;
        $empr->save();

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
        $empresa = Empresa::find($id);
        $empresa->idcrypt = Crypt::encrypt($id);
        return response()->json($empresa);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $empresa = Empresa::find($id);
        $empresa->activo = false;
        $empresa->update();
        return response()->json($empresa);
    }

    public function activar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('active',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $empresa = Empresa::find($id);
        $empresa->activo = true;
        $empresa->update();
        return response()->json($empresa);
    }

}
