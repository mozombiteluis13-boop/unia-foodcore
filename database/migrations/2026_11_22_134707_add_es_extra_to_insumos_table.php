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
        Schema::table('insumos', function (Blueprint $table) {
            if (!Schema::hasColumn('insumos', 'es_extra')) {
                $table->boolean('es_extra')->default(false)->after('precio');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            if (Schema::hasColumn('insumos', 'es_extra')) {
                $table->dropColumn('es_extra');
            }
        });
    }
};