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

        // Financial Information
        $content[] = "FINANCIAL INFORMATION";
        $content[] = "--------------------";
        $content[] = "FICO Score: " . ($this->fico ?? 'Not provided');
        $content[] = "Balance: $" . number_format($this->balance ?? 0, 2);
        $content[] = "Credits: $" . number_format($this->credits ?? 0, 2);
        if (!empty($this->cards_json)) {
            $content[] = "Cards:";
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
        $content[] = "";

        if ($this->notes) {
            $content[] = "NOTES";
            $content[] = "-----";
            $content[] = $this->notes;
        }

        return implode("\n", $content);
    }
}
