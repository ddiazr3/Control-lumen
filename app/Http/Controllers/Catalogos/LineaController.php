<?php

namespace App\Http\Controllers\Catalogos;

use App\Exports\CatalogosExport;
use App\Http\Controllers\Controller;
use App\Imports\CatalogosImport;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Svg\Tag\Line;

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
                        $lineas = Linea::with('marca')->where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $lineas = Linea::with('marca')->where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);

                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $lineas = Linea::with('marca')->paginate(10);
            }else{
                $lineas = Linea::with('marca')->where('empresaid',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($lineas as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }

        $data = [
            "lineas" => $lineas,
            "permisos" => $permisos
        ];

        Log::info($data);
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
            'linea.nombre'    => 'required',
            'linea.marcaid'    => 'required',
        ];

        $messages = [
            'linea.nombre.required'        => 'El nombre es requerido',
            'linea.marcaid.required'        => 'Marca es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->linea['idcrypt']) and $request->linea['idcrypt']){
            $li = Linea::find(Crypt::decrypt($request->linea['idcrypt']));
        }else{
            $li = new Linea();
        }
        $li->nombre = $request->linea['nombre'];
        $li->marcaid = $request->linea['marcaid'];
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
        $marcas = Marca::where('empresaid', Auth::user()->empresaid)->get();

        $data = [
            "marcas" => $marcas
        ];

        return response()->json($data);
    }

    public function getLinea($marcaid){

        $lineas = Linea::where('empresaid', Auth::user()->empresaid)->where('marcaid',$marcaid)->get();

        return response()->json($lineas);
    }

    public function exportar(Request $request){

        $lineas = Linea::with('marca')->where('empresaid',Auth::user()->empresaid);

        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi
            switch ($request->item0) {
                case '1' : $lineas = $lineas->where('nombre','like','%'.$request->datobuscar.'%');
                    break;
            }
        }

        $lineas = $lineas->get();

        $dataExport = [];

        foreach ($lineas as $l){
            $dataExportInstance = [
                "linea" => $l->nombre,
                "marca" => $l->marca->nombre
            ];
            array_push($dataExport, $dataExportInstance);

        }
        $header = ["linea","marca"];

        ob_end_clean();
        return  (new CatalogosExport(collect($dataExport), $header))->download('lineas.xlsx');
    }

    public function import(Request $request){
        if($request->hasFile('file')) {
            $name = $request->file('file')->getClientOriginalName();
            $exte = $request->file('file')->getClientOriginalExtension();
            Excel::import(new CatalogosImport('linea',Auth::user()->empresaid),$request->file('file')->store('temp'));
            $data = [
                'name' => $name,
                'extension' => $exte,
            ];
            return response()->json($data);
        }else{
            return response()->json([
                'message' => "No es un archivo"
            ],405);
        }
    }
}
