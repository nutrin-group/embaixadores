<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('variant_id');
            $table->integer('quantity');
            $table->timestamps();

            // Índices para performance
            $table->index('variant_id');
            $table->index(['order_id', 'variant_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
