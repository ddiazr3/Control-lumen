<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string("nombre");
            $table->string("descripcion");
            $table->boolean("activo")->default(true);
            $table->string("codigo");
            $table->foreignId('proveedorid')
                ->constrained('proveedores')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('marcaid')
                ->constrained('marcas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('lineaid')
                ->constrained('lineas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('categoriaid')
                ->constrained('categorias')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('empresaid')
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
        Schema::dropIfExists('productos');
    }
}
