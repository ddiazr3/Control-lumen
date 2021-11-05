<?php

namespace App\Http\Controllers\Catalogos;

use App\Exports\CatalogosExport;
use App\Http\Controllers\Controller;
use App\Imports\CatalogosImport;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Marcas;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

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
            'marca.nombre'    => 'required',
        ];

        $messages = [
            'marca.nombre.required'        => 'El nombre es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->marca['idcrypt']) and $request->marca['idcrypt']){
            $ma = Marca::find(Crypt::decrypt($request->marca['idcrypt']));
        }else{
            $ma = new Marca();
        }
        $ma->nombre = $request->marca['nombre'];
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

    public function exportar(Request $request){

        $marcas = Marca::where('empresaid',Auth::user()->empresaid);

        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi
            switch ($request->item0) {
                case '1' : $marcas = $marcas->where('nombre','like','%'.$request->datobuscar.'%');
                    break;
            }
        }

        $marcas = $marcas->get();

        $dataExport = [];

        foreach ($marcas as $u){
            $dataExportInstance = [
                "nombre" => $u->nombre
            ];
            array_push($dataExport, $dataExportInstance);

        }
        $header = ["nombre"];

        /*$filename = 'marcas.xlsx';

        Excel::store(new CatalogosExport(collect($dataExport), $header),$filename);

        $file = Storage::get($filename);*/



       /* if ($file) {
           $fileLink = 'data:application/vnd.ms-excel;base64,' . base64_encode($file);
           @chmod(Storage::disk('local')->path($filename), 0755);
           @unlink(Storage::disk('local')->path($filename));
        }*/

//        $fullPath = Storage::disk('local')->path($filename);
//        $storageURL = Storage::url($filename);
//
//        Log::info($storageURL);
//        Log::info($fullPath);
//        return $fullPath;
        ob_end_clean();
        return  (new CatalogosExport(collect($dataExport), $header))->download('marcas.xlsx');
    }

    public function import(Request $request){
        Log::info($request->file('file')->store('temp'));

        if($request->hasFile('file')) {


            $name = $request->file('file')->getClientOriginalName();
            $exte = $request->file('file')->getClientOriginalExtension();
//            Storage::disk('public')->putFileAs('images',$request->file('file'),$name);
//            //forma de como obtener el documento
//            // $contef = Storage::disk('public')->get("images/$name");
//            $url = Storage::url("public/images/$name");

            Excel::import(new CatalogosImport('marca',Auth::user()->empresaid),$request->file('file')->store('temp'));

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
