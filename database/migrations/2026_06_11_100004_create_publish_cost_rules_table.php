<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_cost_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distribution_channel_id')->constrained()->cascadeOnDelete();
            $table->decimal('default_cost', 12, 2)->default(0);
            $table->string('currency', 3)->default('GHS');
            $table->boolean('auto_record')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'distribution_channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_cost_rules');
    }
};
