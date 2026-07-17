<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('publish_job_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('publish_campaign_id')->nullable()->after('publish_job_id')->constrained()->nullOnDelete();
            $table->foreignId('media_asset_id')->nullable()->after('publish_campaign_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('publish_job_id');
            $table->dropConstrainedForeignId('publish_campaign_id');
            $table->dropConstrainedForeignId('media_asset_id');
        });
    }
};
