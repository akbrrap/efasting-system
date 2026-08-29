<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpnameInternalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(strtoupper(auth()->user()->jenis_user), ['ADMINISTRATOR', 'INTERNAL']);
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
            'qty_buku' => ['required', 'integer', 'min:0'],
            'qty_fisik' => ['required', 'integer', 'min:0'],
            'tagging' => ['required', 'string', 'in:Ada,Tidak Ada'],
            'status_penggunaan' => ['required', 'string', 'in:Digunakan,Idle Sementara,Idle Permanen'],
            'kondisi' => ['required', 'string', 'in:Baik,Rusak'],
            'lokasi' => ['required', 'string', 'max:255'],
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
            'nomor_asset.required' => 'Nomor Aset wajib dipilih.',
            'qty_fisik.required' => 'Qty Fisik Aktual wajib diisi.',
            'tagging.required' => 'Status Tagging wajib dipilih.',
            'status_penggunaan.required' => 'Status Penggunaan wajib dipilih.',
            'kondisi.required' => 'Kondisi Fisik wajib dipilih.',
            'lokasi.required' => 'Detail Ruangan Internal wajib diisi.',
        ];
    }
}
