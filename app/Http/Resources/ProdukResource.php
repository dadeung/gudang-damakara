<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProdukResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sku' => $this->sku,
            'nama' => $this->nama,
            'size' => $this->size,
            'warna' => $this->warna,
            'harga' => $this->harga,
            'stok' => $this->stok,
            'diperbarui_pada' => $this->updated_at?->toISO8601String(),
        ];
    }
}
