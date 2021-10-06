<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsuarioCreacionToUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->foreignId('usuariocreacionid')
                ->nullable()
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('usuariocreacionid')
                ->nullable()
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('empresa', function (Blueprint $table) {
            $table->foreignId('usuariocreacionid')
                ->nullable()
                ->constrained('usuario')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('usuario', function (Blueprint $table) {
            //
        });
    }
}
