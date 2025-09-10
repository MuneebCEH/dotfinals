<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_attendances', function (Blueprint $table) {
            $table->id();

            // The user this attendance belongs to
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Check-in/out timestamps (store in UTC)
            $table->timestamp('check_in'); // always required on create
            $table->timestamp('check_out')->nullable();

            // Total hours worked for this session
            $table->decimal('hours_worked', 7, 2)->default(0); // 99999.99 max

            // For session heartbeats / detecting abandoned sessions
            $table->timestamp('last_heartbeat_at')->nullable()->index();

            // Session status ("in" or "out")
            $table->enum('status', ['in', 'out'])->default('in');

            // Optional notes (manual or auto-appended)
            $table->text('notes')->nullable();

            $table->timestamps();

            // Helpful indexes for daily lookups
            $table->index(['user_id', 'check_in']);
            $table->index(['user_id', 'check_in', 'check_out']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_attendances');
    }
};
