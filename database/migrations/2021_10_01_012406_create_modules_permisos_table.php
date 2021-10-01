<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesPermisosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moduloid')
                ->nullable()
                ->constrained('modulos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('permisoid')
                ->nullable()
                ->constrained('permisos')
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
        Schema::dropIfExists('modules_permisos');
    }
}
