<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('title')->nullable();
            $table->string('icon')->nullable();
            $table->string('to')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('orden')->nullable();
            $table->integer('padreId')->nullable();
            $table->foreignId('empresaid')
                ->nullable()
                ->constrained('empresa')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('usuariocreacionid')
                ->nullable()
                ->constrained('usuario')
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
        Schema::dropIfExists('modulos');
    }
}
