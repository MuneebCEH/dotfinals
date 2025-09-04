<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issue_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('lead_issue_id')->constrained('lead_issues')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->text('body');
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('issue_comments');
    }
};
