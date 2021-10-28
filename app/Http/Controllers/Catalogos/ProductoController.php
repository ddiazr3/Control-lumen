<?php

namespace App\Http\Controllers\Catalogos;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Linea;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

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
                        $productos = Producto::where('nombre','like','%'.$request->datobuscar.'%')->paginate(10);
                    }else{
                        $productos = Producto::where('nombre','like','%'.$request->datobuscar.'%')->where('empresaid',Auth::user()->empresaid)->paginate(10);

                    }
                    break;
            }
        }else{
            if(Auth::user()->isGod()){
                $productos = Producto::paginate(10);
            }else{
                $productos = Producto::where('empresaid',Auth::user()->empresaid)->paginate(10);
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
            $prod = Producto::find(Crypt::decrypt($request->producto['idcrypt']));
        }else{
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
        $prod = Producto::find($id);
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
        $marcas = Marca::where('empresaid', Auth::user()->empresaid)->first();
        $lineas = Linea::where('empresaid', Auth::user()->empresaid)->first();
        $proveedores = Proveedor::where('empresaid', Auth::user()->empresaid)->first();
        $categorias = Categoria::where('empresaid', Auth::user()->empresaid)->first();

        $data = [
            "marcas" => $marcas,
            "lineas" => $lineas,
            "proveedores" => $proveedores,
            "categoria" => $categorias
        ];

        return response()->json($data);
    }
}
