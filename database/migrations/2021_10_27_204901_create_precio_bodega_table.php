<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrecioBodegaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('precio_bodega', function (Blueprint $table) {
            $table->id();
            $table->decimal("precio",8,2)->default(0.00);
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
        Schema::dropIfExists('precio_bodega');
    }
}
