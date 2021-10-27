<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSucursalToEmpresaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->boolean("tienesucursal")->default(false);
            $table->integer("cantidadsucursal")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn('tienesucursal', 'cantidadsucursal');
        });
    }
}
