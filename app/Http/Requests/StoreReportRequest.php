<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quote_id' => ['required', 'exists:quotes,id'],
            'reason' => ['required', Rule::in(['spam', 'inappropriate', 'wrong_info', 'copyright', 'other'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
