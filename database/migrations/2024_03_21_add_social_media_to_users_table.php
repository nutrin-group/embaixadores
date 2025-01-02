<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Removendo a coluna instagram_id
            if (Schema::hasColumn('users', 'instagram_id')) {
                $table->dropColumn('instagram_id');
            }

            // Adicionando as novas colunas
            $table->string('social_network')->nullable()->after('email');
            $table->string('social_media_username')->nullable()->after('social_network');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Removendo as novas colunas
            $table->dropColumn(['social_network', 'social_media_username']);

            // Restaurando a coluna original
            $table->string('instagram_id')->nullable();
        });
    }
};
