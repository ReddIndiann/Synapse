<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->date('occurred_at');
            $table->string('reference')->nullable();
            $table->string('currency', 3)->default('GHS');
            $table->decimal('amount', 15, 2);
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->string('base_currency', 3)->default('GHS');
            $table->decimal('base_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
