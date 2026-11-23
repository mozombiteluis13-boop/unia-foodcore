<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_kiosco', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 50); // Bebidas, Golosinas, Snacks, Galletas
            $table->string('nombre', 255);
            $table->decimal('precio', 8, 2);
            $table->integer('stock')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_kiosco');
    }
};