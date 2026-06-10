<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'due_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'type', 'occurred_at']);
            $table->index(['user_id', 'category', 'occurred_at']);
        });

        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'scheduled_at']);
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->index(['user_id', 'category']);
        });

        Schema::table('assistant_messages', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('journal_lines', function (Blueprint $table) {
            $table->index(['journal_entry_id']);
            $table->index(['ledger_account_id', 'entry_type']);
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status', 'due_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type', 'occurred_at']);
            $table->dropIndex(['user_id', 'category', 'occurred_at']);
        });

        Schema::table('publish_jobs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status', 'scheduled_at']);
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'category']);
        });

        Schema::table('assistant_messages', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('journal_lines', function (Blueprint $table) {
            $table->dropIndex(['journal_entry_id']);
            $table->dropIndex(['ledger_account_id', 'entry_type']);
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'occurred_at']);
        });
    }
};
