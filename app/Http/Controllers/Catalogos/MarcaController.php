<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Marcas;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MarcaController extends Controller
{
    private $path = '/catalogos/marcas';

    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "marcas" => [],
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
                        $marcas = Marca::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $marcas = Marca::where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);

                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $marcas = Marca::paginate(10);
            }else{
                $marcas = Marca::where('empresaid',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($marcas as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }

        $data = [
            "marcas" => $marcas,
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
            'marcas.nombre'    => 'required',
        ];

        $messages = [
            'marcas.nombre.required'        => 'El nombre es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->marcas['idcrypt']) and $request->marcas['idcrypt']){
            $ma = Marca::find(Crypt::decrypt($request->marcas['idcrypt']));
        }else{
            $ma = new Marca();
        }
        $ma->nombre = $request->marcas['nombre'];
        $ma->empresaid =  Auth::user()->empresaid ?? 1;
        $ma->save();

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
        $ma = Marca::find($id);
        $ma->idcrypt = Crypt::encrypt($id);
        return response()->json($ma);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $ma = Marca::find($id);
        $ma->delete();
        return response()->json($ma);
    }

}
