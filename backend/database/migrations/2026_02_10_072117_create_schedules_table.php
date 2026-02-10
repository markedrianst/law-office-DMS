<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('schedules', function (Blueprint $table) {
        $table->id();

        $table->foreignId('lawyer_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

        $table->string('title');
        $table->string('case_number')->nullable();
        $table->string('court_location')->nullable();

        $table->text('description')->nullable();
        $table->text('notes')->nullable();

        $table->dateTime('hearing_date');
        $table->dateTime('hearing_end_date')->nullable();

        $table->enum('status', [
            'scheduled',
            'completed',
            'cancelled',
            'rescheduled'
        ])->default('scheduled');

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
