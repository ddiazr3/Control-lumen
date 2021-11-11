<?php

namespace App\Http\Controllers\Principal;

use App\Exports\CatalogosExport;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\EstadoCompra;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\StockBodega;
use App\Models\Usuario;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ComprasController extends Controller
{
    private $path = '/compras';

    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);
        $proveedores = [];
        $categorias = [];
        $marcas = [];
        $lineas = [];

        if(!in_array('index',$permisos[0])){
            $data = [
                "proveedores" => $proveedores,
                "categorias" => $categorias,
                "marcas" => $marcas,
                "lineas" => $lineas,
                "permisos" => []
            ];
            return response()->json($data);
        }

        $proveedores = Proveedor::where('empresaid', Auth::user()->empresaid)->get();
        $categorias = Categoria::where('empresaid', Auth::user()->empresaid)->get();
        $marcas = Marca::where('empresaid', Auth::user()->empresaid)->get();
        $lineas = Linea::where('empresaid', Auth::user()->empresaid)->get();

        $data = [
            "proveedores" => $proveedores,
            "categorias" => $categorias,
            "marcas" => $marcas,
            "lineas" => $lineas,
            "permisos" => $permisos
        ];

        return response()->json($data);
    }

    public function store(Request $request){

        $totalpagado = $request->totalpagado;
        $detalleCompra = $request->detalleCompras;

      //creando la venta
        $compra = new Compra();
        $compra->fechacompra = Carbon::now();
        $compra->totalpagado = $totalpagado;
        $compra->usuarioid = Auth::id();
        $compra->empresaid = Auth::user()->empresaid;
        $compra->estadocompraid = 2;
        $compra->save();

        //creando el detalle de venta
        foreach ($detalleCompra as $item) {
            $detallecompraInstance = new DetalleCompra();
            $detallecompraInstance->productoid = $item['id'];
            $detallecompraInstance->cantidad = $item['cantidad'];
            $detallecompraInstance->precio = $item['precio'];
            $detallecompraInstance->compraid = $compra->id;
            $detallecompraInstance->save();
        }

        //quitando del stock lo vendido

            foreach ($detalleCompra as $item) {
                $stock = StockBodega::
                where('productoid',$item['id'])
                    ->first();
                $stock->cantidad = $stock->cantidad +  $item['cantidad'];
                $stock->update();
            }


        return response()->json("ok");

    }

    public function reporte(Request $request){
        Log::info($request);
        $permisos = Usuario::permisosUsuarioLogeado('/reportes/compras');


        if (!in_array('index', $permisos[0])) {
            $data = [
                "compras" => [],
                "estadosCompras" => [],
                "permisos" => []
            ];
            return response()->json($data);
        }


        $compra = Compra::with(['detalle' => function ($query){
            $query->with('producto');
        },'usuariocreacion','estado']);

        if(isset($request->search) and $request->search){
            if($request->estadoid > 0){
                $compra = $compra->where('estadocompraid',$request->estadoid);
            }
            if($request->fechainicio != "null"){
                $fecha = Carbon::parse($request->fechainicio);
                $compra = $compra->where('fechacompra','>',$fecha);
            }
            if($request->fechafin != "null"){
                $fecha = Carbon::parse($request->fechafin);
                $compra = $compra->where('fechacompra','<',$fecha);
            }
        }else{
            $fecha = Carbon::now();
            $compra = $compra->where('fechacompra','>',$fecha->format('Y-m-d'));
        }

        $compra = $compra->paginate(10);

        if($compra){
            foreach ($compra as $c){
                $c->idcrypt = Crypt::encrypt($c->id);
            }
        }

        $data = [
            "compras" => $compra,
            "estadosCompras" => EstadoCompra::all(),
            "permisos" => $permisos
        ];

        return response()->json($data);
    }

    public function exportar(Request $request){
        $compra = Compra::with(['detalle' => function ($query){
            $query->with('producto');
        },'usuariocreacion','estado']);

        if(isset($request->search) and $request->search){
            if($request->estadoid > 0){
                $compra = $compra->where('estadocompraid',$request->estadoid);
            }
            if($request->fechainicio != "null"){
                $fecha = Carbon::parse($request->fechainicio);
                $compra = $compra->where('fechacompra','>',$fecha);
            }
            if($request->fechafin != "null"){
                $fecha = Carbon::parse($request->fechafin);
                $compra = $compra->where('fechacompra','<',$fecha);
            }
        }else{
            $fecha = Carbon::now();
            $compra = $compra->where('fechacompra','>',$fecha->format('Y-m-d'));
        }

        $compra = $compra->get();

        $dataExport = [];

        foreach ($compra as $c){
            $dataCompra = [
                "fecha" => Carbon::parse($c->fechacompra)->format('Y-m-d'),
                "Pago" => $c->totalpagado,
                "usuario" => $c->usuariocreacion->nombre,
                "estado" => $c->estado->nombre,
                "producto" => "",
                "cantidad" => "",
                "precio" => "",
            ];
            array_push($dataExport, $dataCompra);
            foreach ($c->detalle as $d){
                $dataDetalle = [
                    "fecha" => "",
                    "Pago" => "",
                    "usuario" => "",
                    "estado" => "",
                    "producto" => $d->producto->nombre,
                    "cantidad" => $d->cantidad,
                    "precio" => $d->precio,
                ];
                array_push($dataExport, $dataDetalle);
            }

        }
        $header = ["Fecha","Pago","Usuario Compra","Estado Compra","Producto","Cantidad","Precio"];

        ob_end_clean();
        return  (new CatalogosExport(collect($dataExport), $header))->download('productos.xlsx');
    }

}
