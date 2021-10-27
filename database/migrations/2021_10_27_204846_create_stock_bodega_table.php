<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockBodegaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_bodegas', function (Blueprint $table) {
            $table->id();
            $table->integer("cantidad")->default(0);
            $table->foreignId('productoid')
                ->constrained('productos')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('bodegaid')
                ->constrained('bodegas')
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
        Schema::dropIfExists('stock_bodega');
    }
}
