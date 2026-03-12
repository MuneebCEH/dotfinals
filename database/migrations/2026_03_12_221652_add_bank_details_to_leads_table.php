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
            $table->json('bank_details')->nullable();
        });

        // Migrate existing columns to JSON (if data exists)
        $leads = DB::table('leads')->get();
        foreach ($leads as $lead) {
            if (!empty($lead->bank_name)) {
                $bankDetails = [[
                    'bank_name' => $lead->bank_name,
                    'name_on_card' => $lead->name_on_card ?? '',
                    'card_number' => $lead->card_number ?? '',
                    'exp_date' => $lead->exp_date ?? '',
                    'cvc' => $lead->cvc ?? '',
                    'balance' => $lead->balance ?? '',
                    'available' => $lead->available ?? '',
                    'last_payment_amount' => $lead->last_payment_amount ?? '',
                    'last_payment_date' => $lead->last_payment_date ?? '',
                    'next_payment_amount' => $lead->next_payment_amount ?? '',
                    'next_payment_date' => $lead->next_payment_date ?? '',
                    'credit_limit' => $lead->credit_limit ?? '',
                    'apr' => $lead->apr ?? '',
                    'charge' => $lead->charge ?? '',
                    'tollfree' => $lead->tollfree ?? '',
                ]];
                DB::table('leads')->where('id', $lead->id)->update(['bank_details' => json_encode($bankDetails)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('bank_details');
        });
    }
};
