<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Primeiro, vamos garantir que todos os registros existentes tenham valores válidos
        DB::statement("UPDATE withdrawals SET status = 'pending' WHERE status NOT IN ('pending', 'approved', 'cancelled')");

        // Agora alteramos a coluna para ENUM
        DB::statement("ALTER TABLE withdrawals MODIFY COLUMN status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE withdrawals MODIFY COLUMN status VARCHAR(255)");
    }
};
