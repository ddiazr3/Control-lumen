<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ComprasController extends Controller
{
    private $path = '/compras';


    public function index(Request $request)
    {
        Log::info('llego');
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



}
