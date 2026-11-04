<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 11)->unique();
            $table->string('nama', 200);
            $table->string('size')->nullable();
            $table->string('warna')->nullable();
            $table->unsignedBigInteger('harga');
            $table->unsignedInteger('stok')->default(0);
            $table->timestamps();
        });

        Schema::create('transaksi_stok_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_referensi')->unique();
            $table->string('sku', 11);
            $table->integer('perubahan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
        Schema::dropIfExists('transaksi_stok_log');
    }
};
