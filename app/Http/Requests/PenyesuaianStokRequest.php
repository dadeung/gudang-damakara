<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PenyesuaianStokRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_referensi' => ['required', 'uuid'],
            'perubahan' => ['required', 'integer', 'not_in:0'],
            'alasan' => ['required', 'in:Stok habis, Stok rusak, Penjualan, Retur, Restock, Lainnya'],
        ];
    }
}
