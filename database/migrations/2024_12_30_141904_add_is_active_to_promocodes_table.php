<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToPromocodesTable extends Migration
{
    public function up()
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('promocodes', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
