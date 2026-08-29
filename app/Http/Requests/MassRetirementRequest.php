<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MassRetirementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && strtoupper(auth()->user()->jenis_user) === 'ADMINISTRATOR';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:20480'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File spreadsheet disposal wajib diunggah.',
            'file.mimes' => 'Format file harus berupa CSV (.csv) atau Excel (.xlsx/.xls).',
            'file.max' => 'Ukuran file maksimal adalah 20MB.',
        ];
    }
}
