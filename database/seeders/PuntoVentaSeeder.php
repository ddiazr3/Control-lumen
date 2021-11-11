<?php

namespace Database\Seeders;

use App\Models\PuntoVentas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PuntoVentaSeeder extends Seeder
{

    private $inserts  = [];

    public function run()
    {
       // DB::table('punto_ventas')->truncate();

        //se insert las empresas
        $this->add(1, ' ',' '," ", " ", true, 1);
        //Empresa 2
        $this->add(2, ' ',' '," ", " ", true, 2);
        $this->add(3, ' ',' '," ", " ", false, 2);
        $this->add(4, ' ',' '," ", " ", false, 2);
        $this->add(5, ' ',' '," ", " ", false, 2);
        //Empresa 3
       // $this->add(6, ' ',' '," ", " ", false, 3);

        DB::table('punto_ventas')->insert($this->inserts);
        DB::statement('UPDATE punto_ventas SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($pId, $pNombre,$pDireccion, $pNit, $pTelefono,$pIgualPrincipal,$pEmpresaId){

        $punto_ventas = PuntoVentas::find($pId);

        if(!$punto_ventas){
            $this->inserts[] = [
                'id' => $pId,
                'nombre' => $pNombre,
                'direccion' => $pDireccion,
                'nit' => $pNit,
                'telefono' => $pTelefono,
                'igualprincipal' => $pIgualPrincipal,
                'empresaid' => $pEmpresaId
            ];
        }


    }

}
