<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'userType')) {
                $table->renameColumn('userType', 'type');
            }
            else if (!Schema::hasColumn('users', 'type')) {
                $table->enum('type', ['admin', 'manager', 'ambassador'])->default('ambassador');
            }
            $table->decimal('balance', 10, 2)->default(0);
            $table->foreignId('manager_referral')->nullable()->constrained('users');
            $table->decimal('locked_balance', 10, 2)->default(0)->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'type')) {
                $table->renameColumn('type', 'userType');
            }
            $table->dropColumn(['balance', 'manager_referral', 'locked_balance']);
        });
    }
};
