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
        $this->add(5, 'usuarios','Usuarios','mdi-account',600,'/configuracion/usuarios',$i);
        $j = $i + 4;
        $this->add(6, 'Catálogos','Catálogos','mdi-alpha-c-box',2000);
        $this->add(7, 'marcas','Marcas','mdi-alpha-m-circle',2100,'/catalogos/marcas',$j);
        $this->add(8, 'lineas','Lineas','mdi-alpha-l-circle',2100,'/catalogos/lineas',$j);
        $this->add(9, 'categorias','Categorías','mdi-alpha-c-circle',2100,'/catalogos/categorias',$j);
        $this->add(10, 'proveedores','Proveedores','mdi-alpha-p-circle',2100,'/catalogos/proveedores',$j);
        $this->add(11, 'productos','Productos','mdi-alpha-p-circle',2100,'/catalogos/productos',$j);

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
