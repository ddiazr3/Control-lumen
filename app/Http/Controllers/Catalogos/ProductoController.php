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
use App\Models\PuntoVentas;
use App\Models\StockBodega;
use App\Models\StockPuntoVenta;
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
                    'message' => "C??digo ya existe"
                ], 404);
            }

            $prod = Producto::find(Crypt::decrypt($request->producto['idcrypt']));


        }else{


            $pr = Producto::where('codigo',$request->producto['codigo'])->first();

            if($pr){
                return response()->json([
                    'message' => "C??digo ya existe"
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
        $header = ["Producto","Descripci??n","C??digo","Cantidad","Precio","Marca","Linea","Proveedor","Categoria","Activo"];

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

    public function getProductos(Request $request)
    {

        $proveedorid = $request->proveedorid;
        $categoriaid = $request->categoriaid;
        $marcaid = $request->marcaid;
        $lineaid = $request->lineaid;

        $productos = Producto::with(['precio','stock','proveedor','categoria','marca','linea']);

        if($proveedorid){
            $productos = $productos->where('proveedorid',$proveedorid);
        }

        if($categoriaid){
            $productos = $productos->where('categoriaid',$categoriaid);
        }

        if($marcaid){
            $productos = $productos->where('marcaid',$marcaid);
        }

        if($lineaid){
            $productos = $productos->where('lineaid',$lineaid);
        }

        $productos = $productos->where('empresaid', Auth::user()->empresaid)->get();

        return response()->json($productos);

    }

    public function getProductosVenta(Request $request)
    {

       $select = $request->select == "producto" ? "nombre": "codigo";
       $texto = $request->texto;

       if($texto == null or $texto == ""){
           return response()->json([]);
       }

       if(Auth::user()->puntoventaid){

           $puntoventa = PuntoVentas::find(Auth::user()->puntoventaid);

           if(!$puntoventa->activo){
               return response()->json([]);
           }
           if($puntoventa->igualprincipal){
               $producto = $this->productosBodega($select,$texto);
           }else{
               $producto = $this->productosPuntoVenta($select,$texto);
           }

       }else{
           $producto = $this->productosBodega($select,$texto);
       }

        return response()->json($producto);

    }

    private function productosBodega($select,$texto)
    {
        $producto = Bodega::select('pb.precio','sb.cantidad','p.id','p.nombre')
            ->join('stock_bodegas as sb','sb.bodegaid','=','bodegas.id')
            ->join('precio_bodega as pb','pb.bodegaid','=','bodegas.id')
            ->join('productos as p', function ($join){
                $join->on('p.id','=','sb.productoid');
                $join->on('p.id','=','pb.productoid');
            })
            ->groupBy(['pb.precio','sb.cantidad','p.id','p.nombre'])
            ->where("p.$select","like","%$texto%")
            ->where('bodegas.empresaid',Auth::user()->empresaid)
            ->get();

        return $producto;
    }

    private function productosPuntoVenta($select,$texto)
    {
        $producto = PuntoVentas::select('ppv.precio','spv.cantidad','p.id','p.nombre')
            ->join('stock_punto_ventas as spv','spv.puntoventaid','=','punto_ventas.id')
            ->join('precio_punto_ventas as ppv','ppv.puntoventaid','=','punto_ventas.id')
            ->join('productos as p', function ($join){
                $join->on('p.id','=','ppv.productoid');
                $join->on('p.id','=','spv.productoid');
            })
            ->groupBy(['ppv.precio','spv.cantidad','p.id','p.nombre'])
            ->where("p.$select","like","%$texto%")
            ->where('punto_ventas.id',Auth::user()->puntoventaid)
            ->get();
        return $producto;
    }

    public function saveFaker(Request $request)
    {
        //marca 1
        // http://localhost:5051/api/productos/savefaker?marcaid=1&lineaid=1&proveedorid=1&categoriaid=1&empresaid=1&bodegaid=1&cantidad=25
        //marca 2
        //http://localhost:5051/api/productos/savefaker?marcaid=2&lineaid=0&proveedorid=1&categoriaid=1&empresaid=1&bodegaid=1&cantidad=25

        //cambia la marca y el proveedor
        //http://localhost:5051/api/productos/savefaker?marcaid=7&lineaid=0&proveedorid=2&categoriaid=1&empresaid=1&bodegaid=1&cantidad=25

        //cambios de empresa
        //http://localhost:5051/api/productos/savefaker?marcaid=25&lineaid=0&proveedorid=3&categoriaid=3&empresaid=2&bodegaid=2&cantidad=25

        $marcaid = $request->marcaid;
        $proveedorid = $request->proveedorid;
        $categoriaid = $request->categoriaid;
        $lineaid = $request->lineaid;
        $empresaid = $request->empresaid;
        $bodegaid = $request->bodegaid;
        $cantidad = $request->cantidad;

        for ($i = 0; $i < $cantidad; $i++){
            $producto = new Producto();
            $producto->nombre = "Producto $i";
            $producto->descripcion = "Descripcion $i";
            $producto->proveedorid = $proveedorid;
            $producto->categoriaid = $categoriaid;
            $producto->marcaid = $marcaid;
            $producto->lineaid = $lineaid != 0 ? $lineaid : null;
            $producto->empresaid = $empresaid;
            $producto->save();

            $catidadB = new StockBodega();
            $catidadB->cantidad = 100;
            $catidadB->productoid = $producto->id;
            $catidadB->bodegaid = $bodegaid;
            $catidadB->save();


            $precioB = new precioBodega();
            $precioB->precio = 1000;
            $precioB->productoid = $producto->id;
            $precioB->bodegaid = $bodegaid;
            $precioB->save();
        }

        return response()->json("ok");

    }

}
