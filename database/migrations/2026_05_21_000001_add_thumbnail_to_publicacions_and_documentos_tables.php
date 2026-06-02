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
        Schema::table('publicacions', function (Blueprint $table) {
            $table->string('Thumbnail')->nullable()->after('Archivo');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->string('Thumbnail')->nullable()->after('URL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publicacions', function (Blueprint $table) {
            $table->dropColumn('Thumbnail');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn('Thumbnail');
        });
    }
};
