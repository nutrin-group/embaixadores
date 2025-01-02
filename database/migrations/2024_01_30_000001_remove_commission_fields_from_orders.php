<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'total_commission',
                'locked_commission',
                'available_commission'
            ]);
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_commission', 10, 2)->nullable();
            $table->decimal('locked_commission', 10, 2)->nullable();
            $table->decimal('available_commission', 10, 2)->nullable();
        });
    }
};
