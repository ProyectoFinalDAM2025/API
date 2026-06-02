<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id('IDReporte');
            $table->enum('TipoEntidad', ['Publicacion', 'Oferta', 'Usuario']);
            $table->unsignedBigInteger('IDEntidad');
            $table->unsignedBigInteger('IDUsuario');
            $table->string('Motivo');
            $table->text('Descripcion')->nullable();
            $table->enum('Estado', ['Pendiente', 'Revisado', 'Descartado'])->default('Pendiente');
            $table->timestamp('FechaReporte');
            $table->timestamps();

            $table->foreign('IDUsuario')->references('IDUsuario')->on('users')->onDelete('cascade');
            $table->index(['TipoEntidad', 'IDEntidad']);
        });

        if (Schema::hasTable('reporte_publicacions')) {
            DB::table('reporte_publicacions')
                ->orderBy('IDReportePublicacion')
                ->chunk(100, function ($reportes) {
                    foreach ($reportes as $reporte) {
                        DB::table('reportes')->insert([
                            'TipoEntidad' => 'Publicacion',
                            'IDEntidad' => $reporte->IDPublicacion,
                            'IDUsuario' => $reporte->IDUsuario,
                            'Motivo' => $reporte->Motivo,
                            'Descripcion' => $reporte->Descripcion,
                            'Estado' => $reporte->Estado ?? 'Pendiente',
                            'FechaReporte' => $reporte->FechaReporte,
                            'created_at' => $reporte->created_at,
                            'updated_at' => $reporte->updated_at,
                        ]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
