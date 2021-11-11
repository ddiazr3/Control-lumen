<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;

class EmpresaSeeder extends Seeder
{
    private $inserts  = [];

    public function run()
    {
      //  DB::table('empresa')->truncate();

        //se insert las empresas
        $this->add(1, 'Empresa Pruebas','Guatemala'," ", "58403313", true, false, 0);
        $this->add(2, 'Empresa Pruebas Dos','Guatemala'," ", "58443311", true, true, 3);
       // $this->add(3, 'Empresa Pruebas Tres','Guatemala'," ", "58403313", true, false, 0);
        DB::table('empresa')->insert($this->inserts);
        DB::statement('UPDATE empresa SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($mId, $mNombre,$mDireccion, $mNit, $mTelefono, $mActivo,$mTieneSucursal , $mCantidadSucursal){
        $empresa = Empresa::find($mId);

        if(!$empresa){
            $this->inserts[] = [
                'id' => $mId,
                'nombre' => $mNombre,
                'direccion' => $mDireccion,
                'nit' => $mNit,
                'telefono' => $mTelefono,
                'activo' => $mActivo,
                'tienesucursal' => $mTieneSucursal,
                'cantidadsucursal' => $mCantidadSucursal,
                'usuariocreacionid' => 1
            ];
        }
    }
}
