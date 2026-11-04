<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TambahProdukRequest extends FormRequest
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
            'sku' => ['required', 'string', 'regex:/^[A-Z]{3}\d{4}-[A-Z]$/', 'unique:produk,sku'],
            'nama' => ['required', 'string', 'max:200'],
            'size' => ['nullable', 'string'],
            'warna' => ['nullable', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
