<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('empresa_ramo', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('id_empresa');
        $table->unsignedBigInteger('id_ramo');
        $table->timestamps();

        $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
        $table->foreign('id_ramo')->references('id')->on('ramos')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_ramo');
    }
};
