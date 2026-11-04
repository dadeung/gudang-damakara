<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenyesuaianStokRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_referensi' => ['required', 'uuid'],
            'perubahan' => ['required', 'integer', 'not_in:0'],
            'alasan' => ['required', 'in:Stok habis,Stok rusak,Penjualan,Retur,Restock,Lainnya'],
        ];
    }
}
