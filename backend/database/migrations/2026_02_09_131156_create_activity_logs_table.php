<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // who did it
            $table->string('action'); // e.g., "Logged in", "Deleted User"
            $table->string('model_type')->nullable(); // optional, like User, Case
            $table->unsignedBigInteger('model_id')->nullable(); // optional affected model ID
            $table->string('ip_address', 45)->nullable(); // store IPv4/IPv6
            $table->string('browser')->nullable(); // browser name
            $table->string('platform')->nullable(); // OS
            $table->text('user_agent')->nullable(); // full user agent string
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
