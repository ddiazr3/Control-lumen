<?php

namespace App\Http\Controllers\Principal;

use App\Exports\CatalogosExport;
use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\Empresa;
use App\Models\EstadoVenta;
use App\Models\Producto;
use App\Models\PuntoVentas;
use App\Models\StockBodega;
use App\Models\StockPuntoVenta;
use App\Models\Usuario;
use App\Models\Venta;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class VentasController extends Controller
{
    private $path = '/reportes/ventas';
    public function index(Request $request)
    {
        $permisos = Usuario::permisosUsuarioLogeado($this->path);

        $esPuntoVenta = false;

        if (!in_array('index', $permisos[0])) {
            $data = [
                "ventas" => [],
                "estadosVentas" => [],
                "puntosventas" => [],
                "permisos" => []
            ];
            return response()->json($data);
        }
        $ventas = Venta::with(['detalle' => function ($query){
            $query->with('producto');
        },'usuariocreacion','estado','puntoventa']);

        if(isset($request->search) and $request->search){
            if($request->estadoid > 0){
                $ventas = $ventas->where('estadoventaid',$request->estadoid);
            }
            if($request->fechainicio != "null"){
                $fecha = Carbon::parse($request->fechainicio);
                $ventas = $ventas->where('fechaventa','>',$fecha);
            }
            if($request->fechafin != "null"){
                $fecha = Carbon::parse($request->fechafin);
                $ventas = $ventas->where('fechaventa','<',$fecha);
            }
            if($request->nombre != "null"){
                $ventas = $ventas->where('nombre',$request->nombre);
            }
            if($request->puntoventaid > 0){

                $ventas = $ventas->where('puntoventaid',$request->puntoventaid);
            }
        }else{
            $fecha = Carbon::now();
            $ventas = $ventas->where('fechaventa','>',$fecha->format('Y-m-d'));
        }
        if (!Auth::user()->isGod()) {
            if (Auth::user()->puntoventaid) {
                $esPuntoVenta = true;
                $ventas = $ventas
                    ->where('puntoventaid',Auth::user()->puntoventaid);
            } else {
                $ventas = $ventas
                    ->where('empresaid', Auth::user()->empresaid);
            }
        }
        $ventas = $ventas->paginate(10);

        $puntosventas = [];
        if(!$esPuntoVenta){
            if(Auth::user()->isGod()){
                $puntosventas = PuntoVentas::all();
            }else{
                $puntosventas = PuntoVentas::where('empresaid',Auth::user()->empresaid)->get();
            }
        }

        if($ventas){
            foreach ($ventas as $v){
                $v->idcrypt = Crypt::encrypt($v->id);
            }
        }



        $data = [
            "ventas" => $ventas,
            "estadosVentas" => EstadoVenta::all(),
            "puntosventas" => $puntosventas,
            "permisos" => $permisos
        ];

        return response()->json($data);
    }

    public function store(Request $request){

        $totalcobro = $request->totalcobrado;
        $nit = $request->nit;
        $nombre = $request->nombre;
        $detalleVenta = $request->detalleVenta;

        //creando la venta
        $venta = new Venta();
        $venta->nombre = $nombre;
        $venta->nitcf = $nit;
        $venta->fechaventa = Carbon::now();
        $venta->totalcobrado = $totalcobro;
        $venta->usuarioid = Auth::id();
        $venta->empresaid = Auth::user()->empresaid;
        $venta->puntoventaid = Auth::user()->puntoventaid;
        $venta->estadoventaid = 3;
        $venta->save();

        //creando el detalle de venta
        foreach ($detalleVenta as $item) {
            $detalleventaInstance = new DetalleVenta();
            $detalleventaInstance->productoid = $item['id'];
            $detalleventaInstance->cantidad = $item['cantidadcompra'];
            $detalleventaInstance->precio = $item['precio'];
            $detalleventaInstance->ventaid = $venta->id;
            $detalleventaInstance->save();
        }

        //quitando del stock lo vendido
        if(Auth::user()->puntoventaid){
            $puntoventa = PuntoVentas::find(Auth::user()->puntoventaid);

            if($puntoventa->igualprincipal){
                foreach ($detalleVenta as $item) {
                    $stock = StockBodega::
                    where('productoid',$item['id'])
                        ->first();
                    $stock->cantidad = $stock->cantidad -  $item['cantidadcompra'];
                    $stock->update();
                }
            }else{
                foreach ($detalleVenta as $item) {
                    $stockpv = StockPuntoVenta::
                    where('productoid',$item['id'])
                        ->first();
                    $stockpv->cantidad = $stockpv->cantidad -  $item['cantidadcompra'];
                    $stockpv->update();
                }
            }

        }else{
            foreach ($detalleVenta as $item) {
                $stock = StockBodega::
                where('productoid',$item['id'])
                    ->first();
                $stock->cantidad = $stock->cantidad -  $item['cantidadcompra'];
                $stock->update();
            }
        }

        return response()->json("ok");
    }

    public function exportar(Request $request){
        $esPuntoVenta = false;

        $ventas = Venta::with(['detalle' => function ($query){
            $query->with('producto');
        },'usuariocreacion','estado','puntoventa']);

        if(isset($request->search) and $request->search){
            if($request->estadoid > 0){
                $ventas = $ventas->where('estadoventaid',$request->estadoid);
            }
            if($request->fechainicio != null){
                $fecha = Carbon::parse($request->fechainicio);
                $ventas = $ventas->where('fechaventa','>',$fecha);
            }
            if($request->fechafin != null){
                $fecha = Carbon::parse($request->fechafin);
                $ventas = $ventas->where('fechaventa','<',$fecha);
            }
            if($request->nombre != null){
                $ventas = $ventas->where('nombre',$request->nombre);
            }
            if($request->puntoventaid > 0){

                $ventas = $ventas->where('puntoventaid',$request->puntoventaid);
            }
        }else{
            $fecha = Carbon::now();
            $ventas = $ventas->where('fechaventa','>',$fecha->format('Y-m-d'));
        }
        if (!Auth::user()->isGod()) {
            if (Auth::user()->puntoventaid) {
                $esPuntoVenta = true;
                $ventas = $ventas->where('puntoventaid',Auth::user()->puntoventaid);
            } else {
                $ventas = $ventas->where('empresaid', Auth::user()->empresaid);
            }
        }
        $ventas = $ventas->get();

        $dataExport = [];

        foreach ($ventas as $v){
            $dataVenta = [
                "cliente" => $v->nombre,
                "nit" => $v->nitcf,
                "fecha" => Carbon::parse($v->fechaventa)->format('Y-m-d'),
                "totalcobrado" => $v->totalcobrado,
                "usuario" => $v->usuariocreacion->nombre,
                "estado" => $v->estado->nombre,
                "venta" => $v->puntoventa->nombre,
                "producto" => "",
                "cantidad" => "",
                "precio" => "",
            ];
            array_push($dataExport, $dataVenta);
            foreach ($v->detalle as $d){
                $dataDetalle = [
                    "cliente" => "",
                    "nit" => "",
                    "fecha" => "",
                    "totalcobrado" => "",
                    "usuario" => "",
                    "estado" => "",
                        "venta" => "",
                    "producto" => $d->producto->nombre,
                    "cantidad" => $d->cantidad,
                    "precio" => $d->precio,
                ];
                array_push($dataExport, $dataDetalle);
            }

        }
        $header = ["Cliente","Nit","Fecha","Cobro","Usuario Venta","Estado","Punto de Venta","Producto","Cantidad","Precio"];

        ob_end_clean();
        return  (new CatalogosExport(collect($dataExport), $header))->download('productos.xlsx');
    }
}
