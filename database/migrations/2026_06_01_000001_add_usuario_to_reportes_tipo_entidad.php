<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reportes MODIFY TipoEntidad ENUM('Publicacion', 'Oferta', 'Usuario') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reportes MODIFY TipoEntidad ENUM('Publicacion', 'Oferta') NOT NULL");
    }
};
