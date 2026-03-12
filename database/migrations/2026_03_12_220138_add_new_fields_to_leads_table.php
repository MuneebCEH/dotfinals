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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('cell')->nullable();
            $table->string('dob')->nullable();
            $table->string('mmn')->nullable();
            $table->string('email')->nullable();
            $table->integer('total_cards')->nullable();
            $table->string('total_debt')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('name_on_card')->nullable();
            $table->string('card_number')->nullable();
            $table->string('exp_date')->nullable();
            $table->string('cvc')->nullable();
            $table->string('available')->nullable();
            $table->string('last_payment_amount')->nullable();
            $table->string('last_payment_date')->nullable();
            $table->string('next_payment_amount')->nullable();
            $table->string('next_payment_date')->nullable();
            $table->string('credit_limit')->nullable();
            $table->string('apr')->nullable();
            $table->string('charge')->nullable();
            $table->string('tollfree')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'cell', 'dob', 'mmn', 'email', 'total_cards', 'total_debt',
                'bank_name', 'name_on_card', 'card_number', 'exp_date', 'cvc',
                'available', 'last_payment_amount', 'last_payment_date',
                'next_payment_amount', 'next_payment_date', 'credit_limit',
                'apr', 'charge', 'tollfree'
            ]);
        });
    }
};
