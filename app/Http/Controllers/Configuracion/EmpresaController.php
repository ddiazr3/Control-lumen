<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmpresaController extends Controller
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

        $permisos= Usuario::permisosUsuarioLogeado($userLoged,'/configuracion/empresas');
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
                        $empresas = Empresa::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $empresas = Empresa::where('nombre','like','%'.$request->datobuscar.'%')->where('id',$userLoged->empresaid)->paginate(10);
                    }
                    break;
            }
        }else{
            if($isGod){
                $empresas = Empresa::paginate(10);
            }else{
                $empresas = Empresa::where('id',$userLoged->empresaid)->paginate(10);
            }

        }

        foreach ($empresas as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
            $e->permisos = $permisos;
        }

        return response()->json($empresas);
    }

    public function store(Request $request){

        Log::info($request);

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
        $empr->usuariocreacionid =  isset($request->empresa['usuariocreacionid']) ? Crypt::decrypt($request->empresa['usuariocreacionid']) : null;
        $empr->logo = isset($request->empresa['logo']) ? $request->empresa['logo'] : null;
        $empr->save();

        return response()->json(200);
    }

    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $empresa = Empresa::find($id);
        $empresa->idcrypt = Crypt::encrypt($id);
        return response()->json($empresa);
    }

    public function eliminar($id)
    {

        $empresa = Empresa::find($id);
        $empresa->activo = false;
        $empresa->update();
        return response()->json($empresa);
    }

    public function activar($id)
    {
        $empresa = Empresa::find($id);
        $empresa->activo = true;
        $empresa->update();
        return response()->json($empresa);
    }

}
