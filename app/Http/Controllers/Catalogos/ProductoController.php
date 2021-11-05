<?php

namespace App\Http\Controllers\Catalogos;

use App\Exports\CatalogosExport;
use App\Http\Controllers\Controller;
use App\Imports\CatalogosImport;
use App\Imports\ProductoImport;
use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\PrecioBodega;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockBodega;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    private $path = '/catalogos/productos';

    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "productos" => [],
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
                        $productos = Producto::with(['proveedor','categoria','marca','linea','precio','stock'])->where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $productos = Producto::with(['proveedor','categoria','marca','linea','precio','stock'])->where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);

                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $productos = Producto::with(['proveedor','categoria','marca','linea','precio','stock'])->paginate(10);
            }else{
                $productos = Producto::with(['proveedor','categoria','marca','linea','precio','stock'])->where('empresaid',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($productos as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }


        $data = [
            "productos" => $productos,
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
            'producto.nombre'    => 'required'
        ];

        $messages = [
            'producto.nombre.required'        => 'El nombre es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }




        if(isset($request->producto['idcrypt']) and $request->producto['idcrypt']){

            $pr = Producto::where('id','<>',Crypt::decrypt($request->producto['idcrypt']))->where('codigo',$request->producto['codigo'])->first();

            if($pr){
                return response()->json([
                    'message' => "Código ya existe"
                ], 404);
            }

            $prod = Producto::find(Crypt::decrypt($request->producto['idcrypt']));


        }else{


            $pr = Producto::where('codigo',$request->producto['codigo'])->first();

            if($pr){
                return response()->json([
                    'message' => "Código ya existe"
                ], 404);
            }
            $prod = new Producto();
        }
        $prod->nombre = $request->producto['nombre'];
        $prod->descripcion = $request->producto['descripcion'] ?? null;
        $prod->codigo = $request->producto['codigo'] ?? null;
        $prod->proveedorid = $request->producto['proveedorid'] ?? null;
        $prod->marcaid = $request->producto['marcaid'] ?? null;
        $prod->lineaid = $request->producto['lineaid'] ?? null;
        $prod->categoriaid = $request->producto['categoriaid'] ?? null;
        $prod->empresaid =  Auth::user()->empresaid ?? 1;
        $prod->save();

        $bodega = Bodega::where('empresaid',Auth::user()->empresaid)->first();

        if($request->producto['stockBodega']['id']){
            $catidadB = StockBodega::find($request->producto['stockBodega']['id']);
        }else{
            $catidadB = new StockBodega();
        }
        $catidadB->cantidad = $request->producto['stockBodega']['cantidad'];
        $catidadB->productoid = $prod->id;
        $catidadB->bodegaid = $bodega->id;
        $catidadB->save();

        if($request->producto['precioBodega']['id']){
            $precioB = PrecioBodega::find($request->producto['precioBodega']['id']);
        }else{
            $precioB = new precioBodega();
        }
        $precioB->precio = $request->producto['precioBodega']['precio'];
        $precioB->productoid = $prod->id;
        $precioB->bodegaid = $bodega->id;
        $precioB->save();

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
        $prod = Producto::with(['proveedor','categoria','marca','linea','precio','stock'])->find($id);
        $prod->idcrypt = Crypt::encrypt($id);
        return response()->json($prod);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $prod = Producto::find($id);
        $prod->activo = false;
        $prod->update();
        return response()->json($prod);
    }

    public function activar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $prod = Producto::find($id);
        $prod->activo = true;
        $prod->update();
        return response()->json($prod);
    }

    public function catalogos(){
        $marcas = Marca::where('empresaid', Auth::user()->empresaid)->get();
        $proveedores = Proveedor::where('empresaid', Auth::user()->empresaid)->get();
        $categorias = Categoria::where('empresaid', Auth::user()->empresaid)->get();

        $data = [
            "marcas" => $marcas,
            "proveedores" => $proveedores,
            "categoria" => $categorias
        ];
        return response()->json($data);
    }

    public function exportar(Request $request){

        $pr = Producto::with(['proveedor','categoria','marca','linea','precio','stock'])->where('empresaid',Auth::user()->empresaid);

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
                "producto" => $l->nombre,
                "descripcion" => $l->descripcion,
                "codigo" => $l->codigo,
                "cantidad" => $l->stock->cantidad,
                "precio" => $l->precio->precio,
                "marca" => $l->marca->nombre,
                "linea" => $l->linea->nombre,
                "proveedor" => $l->proveedor->nombre,
                "categoria" => $l->categoria->nombre,
                "activo" => $l->activo ? 'Si':'No'
            ];
            array_push($dataExport, $dataExportInstance);

        }
        $header = ["Producto","Descripción","Código","Cantidad","Precio","Marca","Linea","Proveedor","Categoria","Activo"];

        ob_end_clean();
        return  (new CatalogosExport(collect($dataExport), $header))->download('productos.xlsx');
    }

    public function import(Request $request){
        if($request->hasFile('file')) {
            $name = $request->file('file')->getClientOriginalName();
            $exte = $request->file('file')->getClientOriginalExtension();

            $imporfile = new ProductoImport(Auth::user()->empresaid);
            try {
                $imporfile->import($request->file('file')->store('temp'));
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                $error = null;
                foreach ($failures as $failure) {
//                    $failure->row(); // row that went wrong
//                    $failure->attribute(); // either heading key (if using heading row concern) or column index
                    $error = $failure->errors(); // Actual error messages from Laravel validator
             //       $failure->values(); // The values of the row that has failed.
                }
                return response()->json([
                    'message' => $error[0]
                ],405);
            }
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
