<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
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


        Log::info($request);


        $permisos= Usuario::permisosUsuarioLogeado($this->path);

        if(!in_array('create',$permisos[0])){
            return response()->json([
                'message' => "Unauthorized"
            ],405);
        }

        $rules    = [
            'puntoventa.nombre'    => 'required',
            'puntoventa.direccion' => 'required',
            'puntoventa.nit'       => 'required|numeric',
            'puntoventa.telefono'  => 'required|numeric'
        ];

        $messages = [
            'puntoventa.nombre.required'        => 'El nombre es requerido',
            'puntoventa.direccion.required'     => 'La direccion es requerido',
            'puntoventa.nit.required'           => 'El nit es requerido',
            'puntoventa.nit.numeric'            => 'El nit es campo numerico',
            'puntoventa.telefono.required'      => 'El telefono es requerido',
            'puntoventa.telefono.numeric'       => 'El telefono es campo numerico'
        ];

        $validator = Validator::make($request->all(), $rules, $messages = $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 404);
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

        return response()->json($pv);
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
