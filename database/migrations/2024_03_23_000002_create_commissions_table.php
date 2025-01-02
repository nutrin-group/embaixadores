<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('ambassador_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Índices
            $table->index(['user_id', 'status']);
            $table->index('order_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('commissions');
    }
};
