<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('experiencias', function (Blueprint $table) {
            $table->dropForeign(['id_candidato']);
            $table->foreign('id_candidato')
                ->references('id')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiencias', function (Blueprint $table) {
            $table->dropForeign(['id_candidato']);
            $table->foreign('id_candidato')
                ->references('id')
                ->on('usuarios');
        });
    }
};
