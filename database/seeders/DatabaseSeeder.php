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
        $this->call(EmpresaSeeder::class);
        $this->call(PuntoVentaSeeder::class);
        $this->call(BodegasSeeder::class);
        $this->call(EstadoCompraSeeder::class);
        $this->call(EstadoVentaSeeder::class);
        //$this->call(FakeUsuarioSeeder::class);
        Model::reguard();
        Schema::enableForeignKeyConstraints();
    }
}
