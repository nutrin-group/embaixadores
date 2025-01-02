<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shopify_id')) {
                $table->string('shopify_id')->nullable();
            }

            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable();
            }

            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('pending');
            }

            if (!Schema::hasColumn('orders', 'total_commission')) {
                $table->decimal('total_commission', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('orders', 'locked_commission')) {
                $table->decimal('locked_commission', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('orders', 'available_commission')) {
                $table->decimal('available_commission', 10, 2)->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'shopify_id',
                'customer_name',
                'status',
                'total_commission',
                'locked_commission',
                'available_commission'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
