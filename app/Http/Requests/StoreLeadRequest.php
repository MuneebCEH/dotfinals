<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Normalize inputs before validation.
     * - Map legacy keys if ever sent
     * - Remove empty strings from arrays
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // If any legacy field sneaks in, map it.
        if (array_key_exists('state_abbr', $data) && !array_key_exists('state_abbreviation', $data)) {
            $data['state_abbreviation'] = $data['state_abbr'];
            unset($data['state_abbr']);
        }

        // Normalize numbers[]
        if (isset($data['numbers']) && is_array($data['numbers'])) {
            $data['numbers'] = array_values(array_filter($data['numbers'], fn($v) => filled($v)));
        }

        // Normalize cards_json[]
        if (isset($data['cards_json']) && is_array($data['cards_json'])) {
            $data['cards_json'] = array_values(array_filter($data['cards_json'], fn($v) => filled($v)));
        }

        // Checkbox normalize
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

        $this->replace($data);
    }

    public function rules(): array
    {
        return [
            // Identity
            'first_name'         => ['required', 'string', 'max:255'],
            'middle_initial'     => ['nullable', 'string', 'max:5'],
            'surname'            => ['required', 'string', 'max:255'],
            'gen_code'           => ['nullable', 'string', 'max:255'],

            // Address
            'street'             => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:255'],
            'state_abbreviation' => ['nullable', 'string', 'max:5'],
            'zip_code'           => ['nullable', 'string', 'max:20'],

            // Contact numbers (JSON array)
            'numbers'            => ['nullable', 'array'],
            'numbers.*'          => ['nullable', 'string', 'max:50'],

            // Cards as JSON array (NEW)
            'cards_json'         => ['nullable', 'array'],
            'cards_json.*'       => ['nullable', 'string', 'max:100'],

            // Demographics / custom
            'age'                => ['nullable', 'integer', 'min:0'],
            'xfc06'              => ['nullable', 'string', 'max:255'],
            'xfc07'              => ['nullable', 'string', 'max:255'],
            'demo7'              => ['nullable', 'string', 'max:255'],
            'demo9'              => ['nullable', 'string', 'max:255'],

            // Financial
            'fico'               => ['nullable', 'integer'],
            // 'cards' is intentionally removed (replaced by cards_json)
            'balance'            => ['nullable', 'numeric'],
            'credits'            => ['nullable', 'numeric'],

            // Sensitive
            'ssn'                => ['nullable', 'string', 'max:255'],

            // Category
            'category_id'        => ['nullable', 'exists:categories,id'],

            // Status (fixed set)
            'status'             => [
                'required',
                Rule::in([
                    'Voice Mail',
                    'Wrong Info',
                    'Not Interested',
                    'Deal',
                    'Call Back',
                    'Disconnected Number',
                    'Hangup',
                    'Max Out',
                    'Paid Off',
                    'Not Qualified (NQ)',
                    'Submitted',
                ]),
            ],

            // Assignments with role scoping
            'assigned_to'        => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn($q) => $q->whereIn('role', ['user', 'lead_manager'])),
            ],
            'super_agent_id'     => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'super_agent')),
            ],
            'closer_id'          => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('role', 'closer')),
            ],

            // File upload
            'lead_pdf'           => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'], // 10 MB
            'remove_lead_pdf'    => ['nullable', 'boolean'],

            // Notes
            'notes'              => ['nullable', 'string'],

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

            // New fields
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

    public function messages(): array
    {
        return [
            'super_agent_id.exists' => 'The selected super agent is invalid.',
            'closer_id.exists'      => 'The selected closer is invalid.',
            'assigned_to.exists'    => 'The selected agent is invalid.',
            'lead_pdf.mimetypes'    => 'The uploaded file must be a PDF.',
            'lead_pdf.max'          => 'The PDF may not be greater than 10 MB.',
        ];
    }
}
