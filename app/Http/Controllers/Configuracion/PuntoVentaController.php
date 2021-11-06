<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\PrecioBodega;
use App\Models\precioPuntoVenta;
use App\Models\StockBodega;
use App\Models\StockPuntoVenta;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\PuntoVentas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PuntoVentaController extends Controller
{
    private $path = '/configuracion/puntoventas';
    public function index(Request $request)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('index',$permisos[0])){
            $data = [
                "puntoventas" => [],
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
                        $puntoventas = PuntoVentas::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $puntoventas = PuntoVentas::where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);
                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $puntoventas = PuntoVentas::paginate(10);
            }else{
                $puntoventas = PuntoVentas::where('empresaid',Auth::user()->empresaid)->paginate(10);
            }

        }

        foreach ($puntoventas as $e){
            $e->idcrypt = Crypt::encrypt($e->id);
        }

        $data = [
            "puntoventas" => $puntoventas,
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
            'puntoventa.nombre'    => 'required',
            'puntoventa.direccion' => 'required',
            'puntoventa.nit'       => 'required',
            'puntoventa.telefono'  => 'required|numeric'
        ];

        $messages = [
            'puntoventa.nombre.required'        => 'El nombre es requerido',
            'puntoventa.direccion.required'     => 'La direccion es requerido',
            'puntoventa.nit.required'           => 'El nit es requerido',
            'puntoventa.telefono.required'      => 'El telefono es requerido',
            'puntoventa.telefono.numeric'       => 'El telefono es campo numerico'
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
        }

        //validamos las cantidades que solicitan
        $pr = $request->puntoventa['productos'];
        foreach ($pr as $p) {
            if ($p['checked']) {

                $stokBodega = StockBodega::where('productoid', $p['id'])->first();

                if ($p['cantidad'] > $stokBodega->cantidad) {
                    return response()->json([
                        'message' => "Catindad del producto es mayor a la de bodega ".$p['nombre']
                    ], 404);
                }
            }
        }


        if(isset($request->puntoventa['idcrypt']) and $request->puntoventa['idcrypt']){
            $pv = PuntoVentas::find(Crypt::decrypt($request->puntoventa['idcrypt']));
        }else{
            $pv = new PuntoVentas();
        }
        $pv->nombre = $request->puntoventa['nombre'];
        $pv->direccion = $request->puntoventa['direccion'];
        $pv->nit = $request->puntoventa['nit'];
        $pv->telefono = $request->puntoventa['telefono'];
        $pv->empresaid = Auth::user()->empresaid;
        $pv->save();


        //eliminamos los registro que ya tenia
        $stokPV = StockPuntoVenta::where('puntoventaid',$pv->id)->get();

        if($stokPV){
            $stokPV = StockPuntoVenta::where('puntoventaid',$pv->id)->delete();
            $precioPV = precioPuntoVenta::where('puntoventaid',$pv->id)->delete();
        }


        //volvemos agregar los que vienen en el request


        foreach ($pr as $p){
            if($p['checked']){

                $stokBodega = StockBodega::where('productoid', $p['id'])->first();
                $stokBodega->cantidad = $stokBodega->cantidad - $p['cantidad'];
                $stokBodega->update();

                $stockPuntoVenta = new StockPuntoVenta();
                $stockPuntoVenta->cantidad = $p['cantidad'];
                $stockPuntoVenta->cantidad_bodega = $stokBodega->cantidad;
                $stockPuntoVenta->productoid = $p['id'];
                $stockPuntoVenta->puntoventaid = $pv->id;
                $stockPuntoVenta->save();

                $precioPuntoVenta = new precioPuntoVenta();
                $precioPuntoVenta->precio = $p['precio'];
                $precioPuntoVenta->precio_bodega = $p['precioBodega'];
                $precioPuntoVenta->productoid = $p['id'];
                $precioPuntoVenta->puntoventaid = $pv->id;
                $precioPuntoVenta->save();
            }
        }
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
        $pv = PuntoVentas::find($id);
        $pv->idcrypt = Crypt::encrypt($id);


        $productos = Producto::with(['precio','stock'])->where('empresaid',$pv->empresaid)->get();

        $productoPuntosVentas = [];
        foreach ($productos as $p){

            $stockpv = StockPuntoVenta::where('productoid', $p->id)->where('puntoventaid',$pv->id)->first();
            $preciopv = precioPuntoVenta::where('productoid', $p->id)->where('puntoventaid',$pv->id)->first();
            $pr = [
                "id" =>  $p->id,
                "nombre" => $p->nombre,
                "precioBodega" => $p->precio->precio,
                "stockBodega" => $p->stock->cantidad,
                "cantidad" => $stockpv->cantidad ?? 0,
                "precio" => $preciopv->precio ?? 0.00,
                "checked" => $preciopv ? true : false
            ];

            array_push($productoPuntosVentas, $pr);
        }

        $data = [
            "productos" => $productoPuntosVentas,
            "puntoventa" => $pv
        ];

        return response()->json($data);
    }

    public function eliminar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('desactive',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $pv = PuntoVentas::find($id);
        $pv->activo = false;
        $pv->update();
        return response()->json($pv);
    }

    public function activar($id)
    {
        $permisos= Usuario::permisosUsuarioLogeado($this->path);



        if(!in_array('activate',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }
        $pv = PuntoVentas::find($id);
        $pv->activo = true;
        $pv->update();
        return response()->json($pv);
    }
}
