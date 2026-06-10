<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mail_logs');
        Schema::dropIfExists('messages');
    }

    public function down(): void
    {
        // Stub tables - recreating not needed
    }
};
