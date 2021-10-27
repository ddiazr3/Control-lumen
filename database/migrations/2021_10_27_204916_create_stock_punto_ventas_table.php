<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPuntoVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_punto_ventas', function (Blueprint $table) {
            $table->id();
            $table->integer("cantidad")->default(0);
            $table->double("cantidad_bodega")->default(false);
            $table->foreignId('productoid')
                ->constrained('productos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('puntoventaid')
                ->constrained('punto_ventas')
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
        Schema::dropIfExists('stock_punto_ventas');
    }
}
