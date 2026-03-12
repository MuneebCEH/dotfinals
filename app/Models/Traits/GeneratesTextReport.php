<?php

namespace App\Models\Traits;

trait GeneratesTextReport
{
    public function generateTextReport(): string
    {
        $content = [];
        $content[] = str_repeat('=', 50);
        $content[] = "LEAD DETAILS REPORT";
        $content[] = "Generated: " . now()->format('Y-m-d H:i:s');
        $content[] = str_repeat('=', 50);
        $content[] = "";

        // Personal Information
        $content[] = "PERSONAL INFORMATION";
        $content[] = "-----------------";
        $content[] = "Name: " . trim($this->first_name . ' ' . $this->middle_initial . ' ' . $this->surname);
        $content[] = "SSN: " . ($this->ssn ?? 'Not provided');
        $content[] = "Age: " . ($this->age ?? 'Not provided');
        $content[] = "";

        // Contact Information
        $content[] = "CONTACT INFORMATION";
        $content[] = "------------------";
        $content[] = "Address: " . trim($this->street ?? '');
        $content[] = "City: " . ($this->city ?? 'Not provided');
        $content[] = "State: " . ($this->state_abbreviation ?? 'Not provided');
        $content[] = "ZIP: " . ($this->zip_code ?? 'Not provided');
        if (!empty($this->numbers)) {
            $content[] = "Phone Numbers:";
            foreach ($this->numbers as $number) {
                $content[] = "- " . $number;
            }
        }
        $content[] = "";

        // Financial & Bank Information
        $content[] = "FINANCIAL & BANK INFORMATION";
        $content[] = "---------------------------";
        $content[] = "FICO Score: " . ($this->fico ?? 'Not provided');
        $content[] = "Total Debt: $" . number_format($this->total_debt ?? 0, 2);
        
        $banks = is_array($this->bank_details) ? $this->bank_details : [];
        if (empty($banks)) {
            $banks = [[
                'bank_name' => $this->bank_name,
                'card_number' => $this->card_number,
                'balance' => $this->balance,
                'available' => $this->available,
            ]];
        }

        foreach ($banks as $index => $bank) {
            $bankNum = $index + 1;
            $content[] = "";
            $content[] = "Bank #{$bankNum}";
            $content[] = "Bank Name: " . ($bank['bank_name'] ?? 'Not provided');
            $content[] = "Name on Card: " . ($bank['name_on_card'] ?? 'Not provided');
            $content[] = "Card Number: " . ($bank['card_number'] ?? 'Not provided');
            $content[] = "Exp Date: " . ($bank['exp_date'] ?? 'Not provided');
            $content[] = "CVC: " . ($bank['cvc'] ?? 'Not provided');
            $content[] = "Balance: $" . number_format($bank['balance'] ?? 0, 2);
            $content[] = "Available: $" . number_format($bank['available'] ?? 0, 2);
            $content[] = "Last Payment: $" . number_format($bank['last_payment_amount'] ?? 0, 2) . " on " . ($bank['last_payment_date'] ?? 'N/A');
            $content[] = "Next Payment: $" . number_format($bank['next_payment_amount'] ?? 0, 2) . " on " . ($bank['next_payment_date'] ?? 'N/A');
            $content[] = "Credit Limit: $" . number_format($bank['credit_limit'] ?? 0, 2);
            $content[] = "APR: " . ($bank['apr'] ?? '0.00%');
            $content[] = "Charge: $" . number_format($bank['charge'] ?? 0, 2);
            $content[] = "Tollfree: " . ($bank['tollfree'] ?? 'Not provided');
        }

        if (!empty($this->cards_json)) {
            $content[] = "";
            $content[] = "Other Cards:";
            foreach ($this->cards_json as $card) {
                $content[] = "- " . $card;
            }
        }
        $content[] = "";

        // Additional Information
        $content[] = "ADDITIONAL INFORMATION";
        $content[] = "---------------------";
        $content[] = "Status: " . ($this->status ?? 'Not set');
        // $content[] = "Category: " . ($this->category?->name ?? 'Not categorized');
        $content[] = "Generation Code: " . ($this->gen_code ?? 'Not provided');
        $content[] = "XFC06: " . ($this->xfc06 ?? 'Not provided');
        $content[] = "XFC07: " . ($this->xfc07 ?? 'Not provided');
        $content[] = "Demo7: " . ($this->demo7 ?? 'Not provided');
        $content[] = "Demo9: " . ($this->demo9 ?? 'Not provided');
        $content[] = "";

        // Assignment Information
        $content[] = "ASSIGNMENT INFORMATION";
        $content[] = "---------------------";
        $content[] = "Assigned To: " . ($this->assignee?->name ?? 'Not assigned');
        $content[] = "Super Agent: " . ($this->superAgent?->name ?? 'Not assigned');
        $content[] = "Closer: " . ($this->closer?->name ?? 'Not assigned');
        $content[] = "Agent Name: " . ($this->agent_name ?? 'Not provided');
        $content[] = "TL Name: " . ($this->tl_name ?? 'Not provided');
        $content[] = "Closer Name: " . ($this->closer_name ?? 'Not provided');
        $content[] = "Verifier Name: " . ($this->verifier_name ?? 'Not provided');
        $content[] = "Combined Charge: " . ($this->combined_charge ?? 'Not provided');
        $content[] = "";

        if ($this->notes) {
            $content[] = "NOTES";
            $content[] = "-----";
            $content[] = $this->notes;
        }

        return implode("\n", $content);
    }
}
