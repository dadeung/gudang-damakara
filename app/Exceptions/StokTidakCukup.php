<?php

namespace App\Exceptions;

use RuntimeException;

class StokTidakCukup extends RuntimeException {
    public function __construct(public readonly string $sku, public readonly int $tersedia, public readonly int $diminta) {
        parent::__construct("Stok {$sku} tinggal {$tersedia}, tidak bisa dikurangi {$diminta}.");
    }
}
