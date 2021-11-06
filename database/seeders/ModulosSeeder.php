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

        /**
         * En este comentario colocar el ultimo Id ingresado por favor(sirve para llevar un mejor orden en ModuloPermisosSeeder.php)
         * Y para que no se corran los ids de permisos de perfiles asignados
         *//

        $this->add(1,'principal','Principal','mdi-view-dashboard',100,'/principal');
        $this->add(2,'ventas','Ventas','mdi-alpha-p-circle',300,'/ventas');
        $this->add(3,'compras','Compras','mdi-alpha-p-circle',600,'/compras');

        $this->add(4,'configuracion','Configuración','mdi-cog-outline',5000);
        $i = 4; //contamos el numero de elementos atras
        $this->add(5,'empresas','Empresas','mdi-align-vertical-bottom',5100,'/configuracion/empresas',$i);
        $this->add(6,'puntoventas','Punto de Ventas','mdi-align-vertical-bottom',5200,'/configuracion/puntoventas',$i);
        $this->add(7,'roles','Roles','mdi-key',5300,'/configuracion/roles',$i);
        $this->add(8, 'usuarios','Usuarios','mdi-account',5400,'/configuracion/usuarios',$i);
        $j = $i + 5;
        $this->add(9,'Catálogos','Catálogos','mdi-alpha-c-box',10000);
        $this->add(10,'marcas','Marcas','mdi-alpha-m-circle',10100,'/catalogos/marcas',$j);
        $this->add(11, 'lineas','Lineas','mdi-alpha-l-circle',10200,'/catalogos/lineas',$j);
        $this->add(12, 'categorias','Categorías','mdi-alpha-c-circle',10300,'/catalogos/categorias',$j);
        $this->add(13, 'proveedores','Proveedores','mdi-alpha-p-circle',10400,'/catalogos/proveedores',$j);
        $this->add(14,'productos','Productos','mdi-alpha-p-circle',10500,'/catalogos/productos',$j);

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
