<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
       // $usuarios = Usuario::all();
        $usuarios = DB::select("SELECT * FROM usuario");
        return response()->json($usuarios);
    }
}
