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

            'assigned_to'        => ['nullable', 'exists:users,id'],
            'super_agent_id'     => ['nullable', 'exists:users,id'],
            'closer_id'          => ['nullable', 'exists:users,id'],

            'lead_pdf'           => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
            'remove_lead_pdf'    => ['nullable', 'boolean'],
        ];
    }
}
