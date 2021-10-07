<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private $inserts  = [];

    public function run()
    {
        DB::table('modulos')->truncate();

        $this->add(1, 'principal','Principal','mdi-view-dashboard',300,'/principal');

        $this->add(2, 'configuracion','Configuración','mdi-cog-outline',400);
        $i = 2; //contamos el numero de elementos atras
        $this->add(3, 'empresas','Empresas','mdi-align-vertical-bottom',450,'/configuracion/empresas',$i);
        $this->add(4, 'roles','Roles','mdi-key',500,'/configuracion/roles',$i);
        $this->add(5, 'Usuarios','Usuarios','mdi-account',300,'/configuracion/usuarios',$i);
        DB::table('modulos')->insert($this->inserts);
        DB::statement('UPDATE modulos SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($mId, $mNombre, $mTitle,$mIcon, $mOrder, $mTo = null, $mPadreId = null){
        $this->inserts[] = [
          'id' => $mId,
          'nombre' => $mNombre,
            'title' => $mTitle,
            'icon' => $mIcon,
            'to' => $mTo,
            'orden' => $mOrder,
            'padreId' => $mPadreId
        ];
    }
}
