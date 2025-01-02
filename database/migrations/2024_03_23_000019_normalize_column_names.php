<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Verifica se existe a coluna antiga antes de tentar renomear
            if (Schema::hasColumn('orders', 'shopifyId')) {
                $table->renameColumn('shopifyId', 'shopify_id');
            }

            if (Schema::hasColumn('orders', 'customerId')) {
                $table->renameColumn('customerId', 'customer_id');
            }

            if (Schema::hasColumn('orders', 'customerName')) {
                $table->renameColumn('customerName', 'customer_name');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shopify_id')) {
                $table->renameColumn('shopify_id', 'shopifyId');
            }

            if (Schema::hasColumn('orders', 'customer_id')) {
                $table->renameColumn('customer_id', 'customerId');
            }

            if (Schema::hasColumn('orders', 'customer_name')) {
                $table->renameColumn('customer_name', 'customerName');
            }
        });
    }
};
