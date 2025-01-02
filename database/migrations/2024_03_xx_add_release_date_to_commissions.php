<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->timestamp('release_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn('release_date');
        });
    }
};
