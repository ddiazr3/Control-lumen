<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::statement('DELETE FROM rol_modules_permisos WHERE roleid=1');

        DB::statement('INSERT INTO rol_modules_permisos (roleid, modulepermisoid, created_at, updated_at)
				SELECT 1, id , NOW(), NOW() FROM modules_permisos');
        Schema::enableForeignKeyConstraints();
    }
}
