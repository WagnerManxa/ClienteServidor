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
    Schema::create('vagas', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('ramo_id');
        $table->string('titulo');
        $table->text('descricao');
        $table->integer('experiencia');
        $table->decimal('salario_min', 8, 2);
        $table->decimal('salario_max', 8, 2);
        $table->boolean('ativo')->default(true);
        $table->timestamps();

        $table->foreign('ramo_id')->references('id')->on('ramos')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vagas');
    }
};
