<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
         $this->call(PermisosSeeder::class);
         $this->call(ModulosSeeder::class);
         $this->call(ModuloPermisosSeeder::class);
        Model::reguard();
        Schema::enableForeignKeyConstraints();
    }
}
