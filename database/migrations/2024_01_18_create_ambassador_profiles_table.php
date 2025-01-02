<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ambassador_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nationality');
            $table->string('cpf', 14)->unique();
            $table->date('birth_date');
            $table->string('cep', 9);
            $table->string('street');
            $table->string('number');
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state', 2);
            $table->string('phone', 15);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ambassador_profiles');
    }
};
