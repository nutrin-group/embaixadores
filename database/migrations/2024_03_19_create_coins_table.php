<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Permite valores positivos e negativos com 2 casas decimais
            $table->string('description')->nullable(); // Para registrar o motivo da movimentação
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
