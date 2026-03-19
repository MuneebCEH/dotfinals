<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Optional: Add role-based restrictions here
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        if (array_key_exists('state_abbr', $data) && !array_key_exists('state_abbreviation', $data)) {
            $data['state_abbreviation'] = $data['state_abbr'];
            unset($data['state_abbr']);
        }

        if (isset($data['numbers']) && is_array($data['numbers'])) {
            $data['numbers'] = array_values(array_filter($data['numbers'], fn($v) => filled($v)));
        }

        if (isset($data['cards_json']) && is_array($data['cards_json'])) {
            $data['cards_json'] = array_values(array_filter($data['cards_json'], fn($v) => filled($v)));
        }

        if (isset($data['remove_lead_pdf'])) {
            $data['remove_lead_pdf'] = (bool) $data['remove_lead_pdf'];
        }

        if (isset($data['bank_details']) && is_array($data['bank_details'])) {
            $data['bank_details'] = array_values(array_filter($data['bank_details'], function ($bank) {
                // Keep if any field is filled
                return !empty(array_filter($bank, fn($v) => filled($v)));
            }));

            // Sync first bank entry to legacy single columns
            if (!empty($data['bank_details'])) {
                $first = $data['bank_details'][0];
                $legacyKeys = [
                    'bank_name', 'name_on_card', 'card_number', 'exp_date', 'cvc',
                    'balance', 'available', 'last_payment_amount', 'last_payment_date',
                    'next_payment_amount', 'next_payment_date', 'credit_limit',
                    'apr', 'charge', 'tollfree'
                ];
                foreach ($legacyKeys as $key) {
                    if (isset($first[$key])) {
                        $data[$key] = $first[$key];
                    }
                }
            }
        }

        // Normalize total_cards: empty string to null, otherwise cast to int if possible
        if (isset($data['total_cards'])) {
            if ($data['total_cards'] === '') {
                $data['total_cards'] = null;
            } elseif (is_numeric($data['total_cards'])) {
                $data['total_cards'] = (int) $data['total_cards'];
            }
        }

        $this->replace($data);
    }

    public function rules(): array
    {
        return [
            'first_name'         => ['nullable', 'string', 'max:255'],
            'middle_initial'     => ['nullable', 'string', 'max:5'],
            'surname'            => ['nullable', 'string', 'max:255'],
            'gen_code'           => ['nullable', 'string', 'max:255'],
            'street'             => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:255'],
            'state_abbreviation' => ['nullable', 'string', 'max:5'],
            'zip_code'           => ['nullable', 'string', 'max:20'],
            'notes'            => ['nullable', 'string'],

            'numbers'            => ['nullable', 'array'],
            'numbers.*'          => ['nullable', 'string', 'max:50'],

            'cards_json'         => ['nullable', 'array'],
            'cards_json.*'       => ['nullable', 'string', 'max:100'],

            'age'                => ['nullable', 'integer', 'min:0', 'max:200'],
            'xfc06'              => ['nullable', 'string', 'max:255'],
            'xfc07'              => ['nullable', 'string', 'max:255'],
            'demo7'              => ['nullable', 'string', 'max:255'],
            'demo9'              => ['nullable', 'string', 'max:255'],
            'fico'               => ['nullable', 'integer'],
            'balance'            => ['nullable', 'numeric'],
            'credits'            => ['nullable', 'numeric'],
            'ssn'                => ['nullable', 'string', 'max:255'],

            'category_id'        => ['nullable', 'exists:categories,id'],
            'status'             => ['required', Rule::in(\App\Models\Lead::STATUSES)],

            'assigned_to'        => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn($q) => $q->whereIn('role', ['user', 'lead_manager'])),
            ],
            'super_agent_id'     => ['nullable', 'exists:users,id'],
            'closer_id'          => ['nullable', 'exists:users,id'],

            'lead_pdf'           => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
            'remove_lead_pdf'    => ['nullable', 'boolean'],

            // Bank Details Repeater
            'bank_details'        => ['nullable', 'array'],
            'bank_details.*.bank_name'           => ['nullable', 'string', 'max:255'],
            'bank_details.*.name_on_card'        => ['nullable', 'string', 'max:255'],
            'bank_details.*.card_number'         => ['nullable', 'string', 'max:50'],
            'bank_details.*.exp_date'            => ['nullable', 'string', 'max:20'],
            'bank_details.*.cvc'                 => ['nullable', 'string', 'max:10'],
            'bank_details.*.balance'             => ['nullable', 'string', 'max:100'],
            'bank_details.*.available'           => ['nullable', 'string', 'max:100'],
            'bank_details.*.last_payment_amount' => ['nullable', 'string', 'max:100'],
            'bank_details.*.last_payment_date'   => ['nullable', 'string', 'max:50'],
            'bank_details.*.next_payment_amount' => ['nullable', 'string', 'max:100'],
            'bank_details.*.next_payment_date'   => ['nullable', 'string', 'max:50'],
            'bank_details.*.credit_limit'        => ['nullable', 'string', 'max:100'],
            'bank_details.*.apr'                 => ['nullable', 'string', 'max:20'],
            'bank_details.*.charge'              => ['nullable', 'string', 'max:100'],
            'bank_details.*.tollfree'            => ['nullable', 'string', 'max:50'],

            // Legacy/Single fields (keeping for compatibility but form will send bank_details)
            'cell'                => ['nullable', 'string', 'max:50'],
            'dob'                 => ['nullable', 'string', 'max:20'],
            'mmn'                 => ['nullable', 'string', 'max:255'],
            'email'               => ['nullable', 'email', 'max:255'],
            'total_cards'         => ['nullable', 'integer'],
            'total_debt'          => ['nullable', 'string', 'max:100'],
            'bank_name'           => ['nullable', 'string', 'max:255'],
            'name_on_card'        => ['nullable', 'string', 'max:255'],
            'card_number'         => ['nullable', 'string', 'max:50'],
            'exp_date'            => ['nullable', 'string', 'max:20'],
            'cvc'                 => ['nullable', 'string', 'max:10'],
            'available'           => ['nullable', 'string', 'max:100'],
            'last_payment_amount' => ['nullable', 'string', 'max:100'],
            'last_payment_date'   => ['nullable', 'string', 'max:50'],
            'next_payment_amount' => ['nullable', 'string', 'max:100'],
            'next_payment_date'   => ['nullable', 'string', 'max:50'],
            'credit_limit'        => ['nullable', 'string', 'max:100'],
            'apr'                 => ['nullable', 'string', 'max:20'],
            'charge'              => ['nullable', 'string', 'max:100'],
            'tollfree'            => ['nullable', 'string', 'max:50'],
            'agent_name'          => ['nullable', 'string', 'max:255'],
            'tl_name'             => ['nullable', 'string', 'max:255'],
            'closer_name'         => ['nullable', 'string', 'max:255'],
            'verifier_name'       => ['nullable', 'string', 'max:255'],
            'combined_charge'     => ['nullable', 'string', 'max:100'],
        ];
    }
}
