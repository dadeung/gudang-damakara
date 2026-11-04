<?php

use App\Http\Controllers\Api\V1\ProdukController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/produk', [ProdukController::class, 'index']);
    Route::post('/produk', [ProdukController::class, 'store']);
    Route::get('/produk/{produk:sku}', [ProdukController::class, 'show']);
    Route::post('/produk/{produk:sku}/penyesuaian-stok', [ProdukController::class, 'sesuaikanStok']);
});
