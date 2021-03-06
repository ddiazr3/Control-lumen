<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuloPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    private $inserts = [];
    public function run()
    {
        DB::table('modules_permisos')->truncate();

        //aqui se colocan en orden de como se guardan en modulosSeeder
        // toca estar moviendo los ids
        $this->add(1, 1);
        $this->add(2, 1);
        $this->add(3, 1);
        // modulos padres no se colocan solo los de funcionalidades
        $this->add(5,[1,2,3,4,5]);
        $this->add(6);
        $this->add(7);
         $this->add(8);
        //$this->add(7);Modulo padre no se coloca
        $this->add(10);
        $this->add(11);
        $this->add(12);
        $this->add(13);
        $this->add(14);
        //padre de estos no se colocan
        $this->add(16,[1]);
        $this->add(17,[1]);
        DB::table('modules_permisos')->insert($this->inserts);
        DB::statement('UPDATE modules_permisos SET created_at=NOW(), updated_at=NOW()');
    }

    private function add($aModulo, $aPermisos = [1, 2, 3, 4, 5, 6, 7])
    {
        if (!is_array($aPermisos)) {
            $aPermisos = [$aPermisos];
        }
        foreach ($aPermisos as $permiso) {
            $this->inserts[] = ['moduloid' => $aModulo, 'permisoid' => $permiso];
        }
    }
}
