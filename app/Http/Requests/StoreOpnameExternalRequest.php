<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpnameExternalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(strtoupper(auth()->user()->jenis_user), ['ADMINISTRATOR', 'EKSTERNAL']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_asset' => ['required', 'string', 'max:50'],
            'deskripsi_asset' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'book_qty' => ['required', 'integer', 'min:0'],
            'physic_qty' => ['required', 'integer', 'min:0'],
            'kelengkapan_tagging' => ['required', 'string', 'in:Ada,Tidak Ada'],
            'status' => ['required', 'string', 'in:Digunakan,Idle Sementara,Idle Permanen'],
            'kondisi' => ['required', 'string', 'in:Baik,Rusak'],
            'aktual_loc' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'foto_fisik' => ['nullable'],
            'foto_tagging' => ['nullable'],
        ];
    }

    /**
     * Custom validation messages in Indonesian.
     */
    public function messages(): array
    {
        return [
            'nomor_asset.required' => 'Nomor Aset Eksternal wajib dipilih.',
            'aktual_loc.required' => 'Lokasi Aktual Eksternal wajib dipilih.',
            'physic_qty.required' => 'Qty Fisik Aktual wajib diisi.',
            'kelengkapan_tagging.required' => 'Status Kelengkapan Tagging wajib dipilih.',
            'status.required' => 'Status Penggunaan wajib dipilih.',
            'kondisi.required' => 'Kondisi Fisik wajib dipilih.',
        ];
    }
}
