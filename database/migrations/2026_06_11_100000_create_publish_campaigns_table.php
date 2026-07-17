<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->text('caption')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('queued'); // draft, queued, partial, completed, failed
            $table->boolean('record_cost')->default(false);
            $table->decimal('estimated_cost_per_channel', 12, 2)->nullable();
            $table->string('currency', 3)->default('GHS');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_campaigns');
    }
};
