<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\StockBodega;
use App\Models\Usuario;
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

}
