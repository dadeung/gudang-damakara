<?php

namespace Tests\Feature;

use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Setiap test di bawah ini menerjemahkan satu janji pada openapi.yaml
 * menjadi pemeriksaan yang bisa dijalankan.
 */
class ProdukApiTest extends TestCase
{
    use RefreshDatabase;

    private function produkContoh(int $stok = 10): Produk
    {
        return Produk::create([
            'sku' => 'GKM0003-S',
            'nama' => 'Kemeja Ciangi Gayatri T.Panjang',
            'size' => 'S',
            'warna' => 'Terracota',
            'harga' => 185000,
            'stok' => $stok,
        ]);
    }

    public function test_daftar_produk_mengembalikan_200_dan_larik_data(): void
    {
        $this->produkContoh();

        $this->getJson('/api/v1/produk')
            ->assertOk()
            ->assertJsonStructure(['data' => [['sku', 'nama','size', 'warna', 'harga', 'stok', 'diperbarui_pada']]]);
    }

    public function test_menambah_produk_mengembalikan_201_dan_header_location(): void
    {
        $this->postJson('/api/v1/produk', [
            'sku' => 'GKM0003-M',
            'nama' => 'Kemeja Ciangi Gayatri T.Panjang',
            'size' => 'M',
            'warna' => 'Terracota',
            'harga' => 185000,
        ])
            ->assertCreated()
            ->assertHeader('Location', url('/api/v1/produk/GKM0003-M'))
            ->assertJsonPath('data.stok', 0);
    }

    public function test_produk_tanpa_nama_ditolak_dengan_422_problem_json(): void
    {
        $this->postJson('/api/v1/produk', ['sku' => 'GKM0003-S', 'harga' => 185000])
            ->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('status', 422)
            ->assertJsonStructure(['type', 'title', 'status', 'detail', 'errors' => ['nama']]);
    }

    public function test_sku_yang_tidak_ada_mengembalikan_404_problem_json(): void
    {
        $this->getJson('/api/v1/produk/GKM0005-2XL')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('status', 404);
    }

    public function test_penyesuaian_stok_mengubah_stok(): void
    {
        $produk = $this->produkContoh(10);

        $this->postJson("/api/v1/produk/{$produk->sku}/penyesuaian-stok", [
            'id_referensi' => uuid_create(),
            'perubahan' => -3,
            'alasan' => 'Penjualan',
        ])
            ->assertOk()
            ->assertJsonPath('data.stok', 7);
    }

    public function test_pengurangan_melebihi_stok_ditolak_dengan_409(): void
    {
        $produk = $this->produkContoh(3);

        $this->postJson("/api/v1/produk/{$produk->sku}/penyesuaian-stok", [
            'id_referensi' => uuid_create(),
            'perubahan' => -15,
            'alasan' => 'Penjualan',
        ])
            ->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJsonPath('type', 'https://damakara.test/problem/stok-tidak-cukup');
        
        
    }
}
