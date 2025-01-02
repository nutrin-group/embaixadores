<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->decimal('total_amount', 10, 2)->after('amount');
            $table->decimal('locked_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('available_amount', 10, 2)->default(0)->after('locked_amount');
        });
    }

    public function down()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'locked_amount',
                'available_amount'
            ]);
        });
    }
};
