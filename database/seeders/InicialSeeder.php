<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InicialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert(
            [
                'id'            => 1,
                'nombre'        => 'Super Usuario',
                'descripcion'   => 'Ususario para Dany Diaz',
                'created_at'    => date_create(),
                'updated_at'    => date_create()
            ]
        );

        DB::table('usuario')->insert(
            [
                'id'         => 1,
                'nombre'     => 'Dany',
                'apellido'   => 'Diaz',
                'correo'     => 'danylen1@hotmail.com',
                'telefono'   => '44877801',
                'direccion'  => 'Guatemala',
                'password'   => '$2y$10$zOrPimtXtgVXl/nphcryoeo/mxS0oB6uBQZpmZFIB8M8ad1wc9vMi',
                'created_at' => date_create(),
                'updated_at' => date_create()
            ]
        );
        DB::table('roles_usuarios')->insert([
            'usuarioid' => 1,
            'roleid'     => 1
        ]);

        DB::statement('INSERT INTO rol_modules_permisos (roleid, modulepermisoid, created_at, updated_at)
				SELECT 1, id as modulopermisoid, NOW(), NOW() FROM modules_permisos');
    }
}
