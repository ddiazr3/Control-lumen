<?php

namespace App\Http\Controllers\Catalogos;

use App\Exports\CatalogosExport;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    private $path = '/catalogos/categorias';

    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "categorias" => [],
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
                        $categorias = Categoria::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $categorias = Categoria::where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);

                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $categorias = Categoria::paginate(10);
            }else{
                $categorias = Categoria::where('empresaid',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($categorias as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }

        $data = [
            "categorias" => $categorias,
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
            'categoria.nombre'    => 'required'
        ];

        $messages = [
            'categoria.nombre.required'        => 'El nombre es requerido'
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->categoria['idcrypt']) and $request->categoria['idcrypt']){
            $categoria = Categoria::find(Crypt::decrypt($request->categoria['idcrypt']));
        }else{
            $categoria = new Categoria();
        }
        $categoria->nombre = $request->categoria['nombre'];
        $categoria->empresaid =  Auth::user()->empresaid ?? 1;
        $categoria->save();

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
        $categoria = Categoria::find($id);
        $categoria->idcrypt = Crypt::encrypt($id);
        return response()->json($categoria);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $categoria = Categoria::find($id);
        $categoria->delete();
        return response()->json($categoria);
    }

    public function exportar(Request $request){

        $categorias = Categoria::where('empresaid',Auth::user()->empresaid);

        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi
            switch ($request->item0) {
                case '1' : $categorias = $categorias->where('nombre','like','%'.$request->datobuscar.'%');
                    break;
            }
        }

        $categorias = $categorias->get();

        $dataExport = [];

        foreach ($categorias as $l){
            $dataExportInstance = [
                "categoria" => $l->nombre
            ];
            array_push($dataExport, $dataExportInstance);

        }
        $header = ["categoria"];

        ob_end_clean();
        return  (new CatalogosExport(collect($dataExport), $header))->download('categorias.xlsx');
    }
}
