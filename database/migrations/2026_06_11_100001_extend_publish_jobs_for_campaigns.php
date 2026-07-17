<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->foreignId('publish_campaign_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->json('platform_options')->nullable()->after('caption');
            $table->string('external_post_id')->nullable()->after('published_url');
            $table->text('error_message')->nullable()->after('logs');
        });
    }

    public function down(): void
    {
        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('publish_campaign_id');
            $table->dropColumn(['platform_options', 'external_post_id', 'error_message']);
        });
    }
};
