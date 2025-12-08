<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Use your own authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-\.\,&()]+$/',
            'description' => 'nullable|string|max:5000',
            'client_id' => 'required|exists:clients,id',
            'status_id' => 'required|exists:quotation_statuses,id',
            'labor_fee' => 'nullable|numeric|min:0|max:999999.99',
            'delivery_fee' => 'nullable|numeric|min:0|max:999999.99',
            'latest_progress' => 'nullable|string|max:1000',
            'parent_quotation_id' => 'nullable|exists:quotations,id',
            'quotation_type' => 'required|in:standalone,addon',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'The quotation subject is required.',
            'subject.regex' => 'The subject can only contain letters, numbers, spaces, hyphens, dots, commas, and ampersands.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'The selected client is invalid.',
            'status_id.required' => 'Please select a quotation status.',
            'status_id.exists' => 'The selected status is invalid.',
            'labor_fee.numeric' => 'Labor fee must be a valid number.',
            'labor_fee.min' => 'Labor fee cannot be negative.',
            'labor_fee.max' => 'Labor fee is too large.',
            'delivery_fee.numeric' => 'Delivery fee must be a valid number.',
            'delivery_fee.min' => 'Delivery fee cannot be negative.',
            'delivery_fee.max' => 'Delivery fee is too large.',
            'parent_quotation_id.exists' => 'The selected parent quotation is invalid.',
            'quotation_type.in' => 'The quotation type must be either standalone or addon.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove commas from price fields if they exist
        if ($this->has('labor_fee')) {
            $this->merge([
                'labor_fee' => str_replace(',', '', $this->labor_fee),
            ]);
        }

        if ($this->has('delivery_fee')) {
            $this->merge([
                'delivery_fee' => str_replace(',', '', $this->delivery_fee),
            ]);
        }

        // Set default quotation_type if not provided
        if (!$this->has('quotation_type')) {
            $this->merge([
                'quotation_type' => 'standalone',
            ]);
        }
    }
}
