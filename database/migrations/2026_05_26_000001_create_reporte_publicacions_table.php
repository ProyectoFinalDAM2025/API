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
        Schema::create('reporte_publicacions', function (Blueprint $table) {
            $table->id('IDReportePublicacion');
            $table->unsignedBigInteger('IDPublicacion');
            $table->unsignedBigInteger('IDUsuario');
            $table->string('Motivo');
            $table->text('Descripcion')->nullable();
            $table->enum('Estado', ['Pendiente', 'Revisado', 'Descartado'])->default('Pendiente');
            $table->timestamp('FechaReporte');
            $table->timestamps();

            $table->foreign('IDPublicacion')->references('IDPublicacion')->on('publicacions')->onDelete('cascade');
            $table->foreign('IDUsuario')->references('IDUsuario')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_publicacions');
    }
};
