<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('direccion');
            $table->string('nit')->unique();
            $table->string('telefono')->unique();
            $table->timestamps();
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('dpi')->nullable()->unique();
            $table->string('telefono')->unique();
            $table->string('direccion');
            $table->string('token')->nullable();
            $table->foreignId('empresaid')
                ->nullable()
                ->constrained('empresa')
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
        // eliminar una clave foranea $table->dropForeign('posts_user_id_foreign');
        Schema::dropIfExists('empresa');
        Schema::dropIfExists('usuario');
    }
}
