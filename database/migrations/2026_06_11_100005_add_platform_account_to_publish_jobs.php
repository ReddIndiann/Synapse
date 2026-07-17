<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->foreignId('user_platform_account_id')->nullable()->after('distribution_channel_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_platform_account_id');
        });
    }
};
