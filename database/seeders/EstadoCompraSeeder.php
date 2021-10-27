<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoCompraSeeder extends Seeder
{
    private $inserts  = [];

    public function run()
    {
        DB::table('estado_compras')->truncate();

        $this->add(1, 'Pendiente');
        $this->add(2, 'Autorizado');
        $this->add(3, 'Rechazado');
        DB::table('estado_compras')->insert($this->inserts);
        DB::statement('UPDATE estado_compras SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($mId, $mNombre){
        $this->inserts[] = [
            'id' => $mId,
            'nombre' => $mNombre
        ];
    }
}
