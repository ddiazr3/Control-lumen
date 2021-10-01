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

        $this->add(1, 1);
        //modulos padres no se colocan solo los de funcionalidades
        $this->add(3);
        $this->add(4);

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
