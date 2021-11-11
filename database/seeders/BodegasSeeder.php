<?php

namespace Database\Seeders;

use App\Models\Bodega;
use App\Models\PuntoVentas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BodegasSeeder extends Seeder
{

     private $inserts  = [];

    public function run() {
        // DB::table('punto_ventas')->truncate();

        //se insert las bodegas a las empresas
        $this->add(1, ' ',' '," ", " ", true, 1); //Bodega Empresa 1
        $this->add(2, ' ',' '," ", " ", true, 2); //Bodega Empresa 2
       // $this->add(3, ' ',' '," ", " ", true, 3); //Bodega Empresa 3
        DB::table('bodegas')->insert($this->inserts);
        DB::statement('UPDATE bodegas SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($pId, $pNombre,$pDireccion, $pNit, $pTelefono,$pIgualPrincipal,$pEmpresaId){

        $bodega= Bodega::find($pId);

        if(!$bodega){
            $this->inserts[] = [
                'id' => $pId,
                'nombre' => $pNombre,
                'direccion' => $pDireccion,
                'nit' => $pNit,
                'telefono' => $pTelefono,
                'igualempresa' => $pIgualPrincipal,
                'empresaid' => $pEmpresaId
            ];
        }
    }
}
