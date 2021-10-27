<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    private $inserts  = [];

    public function run()
    {
        DB::table('empresa')->truncate();

        $this->add(1, 'Empresa Pruebas','Guatemala',null, "58403313", true, false, 0);
        DB::table('empresa')->insert($this->inserts);
        DB::statement('UPDATE empresa SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($mId, $mNombre,$mDireccion, $mNit, $mTelefono, $mActivo,$mTieneSucursal , $mCantidadSucursal){
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
