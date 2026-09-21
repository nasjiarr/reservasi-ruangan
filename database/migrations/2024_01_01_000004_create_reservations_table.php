<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule')->nullable(); // contoh: "daily", "weekly"
            $table->foreignId('parent_reservation_id')->nullable()
                ->constrained('reservations')->nullOnDelete();
            $table->timestamps();

            // Index krusial untuk mempercepat query conflict detection
            $table->index(['room_id', 'start_time', 'end_time'], 'idx_room_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
