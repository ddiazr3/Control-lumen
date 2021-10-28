<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class LineaController extends Controller
{
    private $path = '/catalogos/lineas';

    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "lineas" => [],
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
                        $lineas = Linea::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $lineas = Linea::where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);

                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $lineas = Linea::paginate(10);
            }else{
                $lineas = Linea::where('empresaid',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($lineas as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }

        $data = [
            "lineas" => $lineas,
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
            'lineas.nombre'    => 'required',
            'lineas.marcaid'    => 'required',
        ];

        $messages = [
            'lineas.nombre.required'        => 'El nombre es requerido',
            'lineas.marcaid.required'        => 'Marca es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->lineas['idcrypt']) and $request->lineas['idcrypt']){
            $li = Linea::find(Crypt::decrypt($request->lineas['idcrypt']));
        }else{
            $li = new Linea();
        }
        $li->nombre = $request->lineas['nombre'];
        $li->marcaid = $request->lineas['marcaid'];
        $li->empresaid =  Auth::user()->empresaid ?? 1;
        $li->save();

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
        $li = Linea::find($id);
        $li->idcrypt = Crypt::encrypt($id);
        return response()->json($li);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $li = Linea::find($id);
        $li->delete();
        return response()->json($li);
    }

    public function catalogos(){
        $marcas = Marca::where('empresaid', Auth::user()->empresaid)->first();

        $data = [
            "marcas" => $marcas
        ];

        return response()->json($data);
    }
}
