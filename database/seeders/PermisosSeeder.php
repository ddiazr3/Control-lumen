<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permisos')->truncate();

        DB::table('permisos')->insert([
            [
                'id' => 1,
                'nombre' => 'index',
                'nombrefriendly' => 'Ver'
            ],
            [
                'id' => 2,
                'nombre' => 'create',
                'nombrefriendly' => 'Crear'
            ],
            [
                'id' => 3,
                'nombre' => 'edit',
                'nombrefriendly' => 'Editar'
            ],
            [
                'id' => 4,
                'nombre' => 'update',
                'nombrefriendly' => 'Actualizar'
            ],
            [
                'id' => 5,
                'nombre' => 'destroy',
                'nombrefriendly' => 'Borrar'
            ],
            [
                'id' => 6,
                'nombre' => 'activate',
                'nombrefriendly' => 'Activar'
            ],
            [
                'id' => 7,
                'nombre' => 'desactive',
                'nombrefriendly' => 'Desactivar'
            ]
        ]);
        DB::statement('UPDATE permisos SET created_at=NOW(), updated_at=NOW()');
    }
}
