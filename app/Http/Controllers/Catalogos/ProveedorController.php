<?php

namespace App\Http\Controllers\Catalogos;

use App\Exports\CatalogosExport;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class ProveedorController extends Controller
{
    private $path = '/catalogos/proveedores';

    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "proveedores" => [],
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
                        $proveedores = Proveedor::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $proveedores = Proveedor::where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);

                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $proveedores = Proveedor::paginate(10);
            }else{
                $proveedores = Proveedor::where('empresaid',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($proveedores as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }

        $data = [
            "proveedores" => $proveedores,
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
            'proveedor.nombre'    => 'required',
            'proveedor.direccion'    => 'required',
            'proveedor.telefono'    => 'required',
        ];

        $messages = [
            'proveedor.nombre.required'        => 'El nombre es requerido',
            'proveedor.direccion.required'        => 'La direccion es requerido',
            'proveedor.telefono.required'        => 'El telefono es requerido'
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }
        if(isset($request->proveedor['idcrypt']) and $request->proveedor['idcrypt']){
            $proveedor = Proveedor::find(Crypt::decrypt($request->proveedor['idcrypt']));
        }else{
            $proveedor = new Proveedor();
        }
        $proveedor->nombre = $request->proveedor['nombre'];
        $proveedor->direccion = $request->proveedor['direccion'];
        $proveedor->telefono = $request->proveedor['telefono'];
        $proveedor->empresaid =  Auth::user()->empresaid ?? 1;
        $proveedor->save();

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
        $pr = Proveedor::find($id);
        $pr->idcrypt = Crypt::encrypt($id);
        return response()->json($pr);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $pr = Proveedor::find($id);
        $pr->delete();
        return response()->json($pr);
    }

    public function exportar(Request $request){

        $pr = Proveedor::where('empresaid',Auth::user()->empresaid);

        if (isset($request->search))
        {
            // 1 nombre, 2 telefono, 3 Dpi
            switch ($request->item0) {
                case '1' : $pr = $pr->where('nombre','like','%'.$request->datobuscar.'%');
                    break;
            }
        }

        $pr = $pr->get();

        $dataExport = [];

        foreach ($pr as $l){
            $dataExportInstance = [
                "proveedor" => $l->nombre,
                "direccion" => $l->direccion,
                "telefono" => $l->telefono
            ];
            array_push($dataExport, $dataExportInstance);

        }
        $header = ["proveedor","direccion","telefono"];

        ob_end_clean();
        return  (new CatalogosExport(collect($dataExport), $header))->download('proveedores.xlsx');
    }
}
