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
        Schema::create('lead_issues', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $t->enum('status', ['open', 'triaged', 'in_progress', 'resolved', 'closed'])->default('open');
            $t->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $t->string('title');
            $t->text('description');
            $t->json('attachments')->nullable();
            $t->text('resolution')->nullable();
            $t->foreignId('resolver_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('resolved_at')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_issues');
    }
};
