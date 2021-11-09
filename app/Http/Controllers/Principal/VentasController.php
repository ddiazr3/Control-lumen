<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\StockBodega;
use App\Models\StockPuntoVenta;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VentasController extends Controller
{
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
            $detalleventaInstance->cantidad = $item['cantidad'];
            $detalleventaInstance->precio = $item['precio'];
            $detalleventaInstance->ventaid = $venta->id;
            $detalleventaInstance->save();
        }

        //quitando del stock lo vendido
        if(Auth::user()->puntoventaid){
            foreach ($detalleVenta as $item) {
                $stockpv = StockPuntoVenta::
                            where('productoid',$item['id'])
                            ->first();
                $stockpv->cantidad = $stockpv->cantidad -  $item['cantidadcompra'];
                $stockpv->update();
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
}
