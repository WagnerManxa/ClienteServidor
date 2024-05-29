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
        Schema::create('experiencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_candidato');
            $table->string('nome_empresa');
            $table->date('inicio');
            $table->date('fim');
            $table->string('cargo');
            $table->timestamps();

            $table->foreign('id_candidato')->references('id')->on('usuarios') ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiencias');
    }
};
