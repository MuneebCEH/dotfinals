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
        Schema::create('issue_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_issue_id')->constrained('lead_issues')->cascadeOnDelete();
            $t->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $t->string('type'); // status_changed, priority_changed, comment_added
            $t->json('meta')->nullable(); // {"from":"open","to":"triaged"}
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_events');
    }
};
