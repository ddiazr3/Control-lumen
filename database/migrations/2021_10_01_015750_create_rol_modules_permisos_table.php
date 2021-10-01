<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolModulesPermisosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rol_modules_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roleid')
                ->nullable()
                ->constrained('modulos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('modulepermisoid')
                ->nullable()
                ->constrained('modules_permisos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rol_modules_permisos');
    }
}
