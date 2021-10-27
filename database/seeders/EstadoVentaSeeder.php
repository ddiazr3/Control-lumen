<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoVentaSeeder extends Seeder
{

    private $inserts  = [];

    public function run()
    {
        DB::table('estado_ventas')->truncate();

        $this->add(1, 'Pendiente');
        $this->add(2, 'Cancelado');
        $this->add(3, 'Completado');
        $this->add(4, 'Cotización');
        DB::table('estado_ventas')->insert($this->inserts);
        DB::statement('UPDATE estado_ventas SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($mId, $mNombre){
        $this->inserts[] = [
            'id' => $mId,
            'nombre' => $mNombre
        ];
    }

}
