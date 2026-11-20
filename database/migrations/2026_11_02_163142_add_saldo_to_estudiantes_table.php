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
    Schema::table('estudiantes', function (Blueprint $table) {
        $table->decimal('saldo', 8, 2)->default(0.00)->after('dni'); // Ajusta el 'after' según tus campos
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            //
        });
    }
};
