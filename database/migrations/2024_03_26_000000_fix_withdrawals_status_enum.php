<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Primeiro, convertemos os status antigos para os novos
        DB::statement("UPDATE withdrawals SET status = 'approved' WHERE status = 'completed'");
        DB::statement("UPDATE withdrawals SET status = 'cancelled' WHERE status = 'rejected'");

        // Agora alteramos a coluna para o novo ENUM
        DB::statement("ALTER TABLE withdrawals MODIFY COLUMN status ENUM('pending', 'approved', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        // No down, voltamos para os status originais
        DB::statement("ALTER TABLE withdrawals MODIFY COLUMN status ENUM('pending', 'completed', 'rejected') NOT NULL DEFAULT 'pending'");

        // Convertemos os status de volta
        DB::statement("UPDATE withdrawals SET status = 'completed' WHERE status = 'approved'");
        DB::statement("UPDATE withdrawals SET status = 'rejected' WHERE status = 'cancelled'");
    }
};
