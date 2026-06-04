<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropUnique('ledger_accounts_code_unique');
            $table->unique(['user_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('ledger_accounts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'code']);
            $table->unique('code');
        });
    }
};
