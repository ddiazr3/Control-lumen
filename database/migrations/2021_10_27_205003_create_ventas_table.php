<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string("nombre")->nullable();
            $table->string("nitcf")->nullable();
            $table->dateTime("fechaventa");
            $table->decimal("totalcobrado",8,2);
            $table->foreignId('clienteid')
                ->nullable()
                ->constrained('clientes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('usuarioid')
                ->nullable()
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('empresaid')
                ->nullable()
                ->constrained('empresa')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('estadoventaid')
                ->constrained('estado_ventas')
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
        Schema::dropIfExists('ventas');
    }
}
