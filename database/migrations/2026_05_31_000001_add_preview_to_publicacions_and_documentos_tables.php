<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('publicacions', 'Preview')) {
            Schema::table('publicacions', function (Blueprint $table) {
                $table->string('Preview')->nullable()->after('Thumbnail');
            });
        }

        if (!Schema::hasColumn('documentos', 'Preview')) {
            Schema::table('documentos', function (Blueprint $table) {
                $table->string('Preview')->nullable()->after('Thumbnail');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('publicacions', 'Preview')) {
            Schema::table('publicacions', function (Blueprint $table) {
                $table->dropColumn('Preview');
            });
        }

        if (Schema::hasColumn('documentos', 'Preview')) {
            Schema::table('documentos', function (Blueprint $table) {
                $table->dropColumn('Preview');
            });
        }
    }
};
