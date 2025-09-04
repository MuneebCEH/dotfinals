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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('first_name')->nullable();
            $table->string('middle_initial', 5)->nullable();
            $table->string('surname')->nullable();
            $table->string('gen_code')->nullable();

            // Address
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state_abbreviation', 5)->nullable();
            $table->string('zip_code', 20)->nullable();

            // Contact numbers (stored as JSON array)
            $table->json('numbers')->nullable();

            // Cards (stored as JSON array)
            $table->json('cards_json')->nullable();

            // Demographics / custom fields
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('xfc06')->nullable();
            $table->string('xfc07')->nullable();
            $table->string('demo7')->nullable();
            $table->string('demo9')->nullable();

            // Financial
            $table->integer('fico')->nullable();
            $table->bigInteger('balance')->nullable();
            $table->bigInteger('credits')->nullable();
            $table->string('lead_pdf_path')->nullable();

            // Sensitive
            $table->text('ssn')->nullable();

            // Category
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            // Status
            $table->string('status', 100)->default('Submitted');

            // Assignments
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('super_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closer_id')->nullable()->constrained('users')->nullOnDelete();

            // Notes
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'category_id']);
            $table->index(['assigned_to', 'super_agent_id', 'closer_id']);
            $table->index('surname');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
