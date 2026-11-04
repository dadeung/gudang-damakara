<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Produk;
use App\Http\Problem;
use App\Exceptions\StokTidakCukup;
use App\Http\Controllers\Controller;
use App\Http\Requests\PenyesuaianStokRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdukController extends Controller {
    public function index() {
        return response()->json(['data' => Produk::orderBy('sku')->get()->map(fn($p) => [
            'sku' => $p->sku,
            'nama' => $p->nama,
            'size' => $p->size,
            'warna' => $p->warna,
            'harga' => $p->harga,
            'stok' => $p->stok,
            'diperbarui_pada' => $p->updated_at->toIso8601String()
        ])]);
    }

    public function show($sku, Request $request) {
        $produk = Produk::where('sku', $sku)->first();
        if (!$produk) {
            return Problem::response($request, 404, 'tidak-ditemukan', 'Sumber daya tidak ditemukan', 'Produk dengan SKU tersebut tidak terdaftar.');
        }
        return response()->json(['data' => [
            'sku' => $produk->sku,
            'nama' => $produk->nama,
            'size' => $produk->size,
            'warna' => $produk->warna,
            'harga' => $produk->harga,
            'stok' => $produk->stok,
            'diperbarui_pada' => $produk->updated_at->toIso8601String()
        ]]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'regex:/^[A-Z]{3}\d{4}-[A-Z]$/', 'unique:produk,sku'],
            'nama' => ['required', 'string', 'max:120'],
            'size' => ['nullable', 'string'],
            'warna' => ['nullable', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
        ]);

        $produk = Produk::create($validated + ['stok' => 0]);

        return response()->json(['data' => [
            'sku' => $produk->sku,
            'nama' => $produk->nama,
            'size' => $produk->size,
            'warna' => $produk->warna,
            'harga' => $produk->harga,
            'stok' => $produk->stok,
            'diperbarui_pada' => $produk->updated_at->toIso8601String()
        ]], 201)->header('Location', url("/api/v1/produk/{$produk->sku}"));
    }

    public function sesuaikanStok(PenyesuaianStokRequest $request, $sku) {
        $produk = Produk::where('sku', $sku)->first();
        if (!$produk) {
            return Problem::response($request, 404, 'tidak-ditemukan', 'Sumber daya tidak ditemukan', 'Gagal memutasi stok. SKU tidak ada.');
        }

        $idRef = $request->input('id_referensi');
        $perubahan = $request->integer('perubahan');

        try {
            $produkModifikasi = DB::transaction(function () use ($produk, $idRef, $perubahan) {
                // Mekanisme Idempotensi: Cek apakah token transaksi id_referensi sudah pernah sukses diproses.
                $sudahAda = DB::table('transaksi_stok_log')->where('id_referensi', $idRef)->first();
                if ($sudahAda) {
                    return Produk::find($produk->id); // Aman untuk retry, langsung kembalikan stok saat ini.
                }

                // Kunci Baris Database (Pessimistic Locking) untuk mencegah data menimpa secara konkuren
                $terkini = Produk::where('id', $produk->id)->lockForUpdate()->first();

                if ($terkini->stok + $perubahan < 0) {
                    throw new StokTidakCukup($terkini->sku, $terkini->stok, abs($perubahan));
                }

                $terkini->increment('stok', $perubahan);

                DB::table('transaksi_stok_log')->insert([
                    'id_referensi' => $idRef,
                    'sku' => $terkini->sku,
                    'perubahan' => $perubahan,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return $terkini->refresh();
            });

            // Simulasi Pengiriman Event Asinkron ke RabbitMQ
            Log::info("Event StokDisesuaikan dipublikasikan ke Exchange damakara.events", [
                'sku' => $produkModifikasi->sku,
                'stok_akhir' => $produkModifikasi->stok
            ]);

            return response()->json(['data' => [
                'sku' => $produkModifikasi->sku,
                'nama' => $produkModifikasi->nama,
                'size' => $produkModifikasi->size,
                'warna' => $produkModifikasi->warna,
                'harga' => $produkModifikasi->harga,
                'stok' => $produkModifikasi->stok,
                'diperbarui_pada' => $produkModifikasi->updated_at->toIso8601String()
            ]]);

        } catch (StokTidakCukup $e) {
            return Problem::response($request, 404, 'stok-tidak-cukup', 'Stok tidak cukup', $e->getMessage());
        }
    }
}
