<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->dateTime("fechacompra");
            $table->decimal("totalpagado",8,2);
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
            $table->foreignId('estadocompraid')
                ->constrained('estado_compras')
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
        Schema::dropIfExists('compras');
    }
}
