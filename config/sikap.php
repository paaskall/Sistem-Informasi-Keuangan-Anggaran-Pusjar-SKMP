<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tahun Anggaran Berjalan
    |--------------------------------------------------------------------------
    |
    | Tahun yang masih bisa diedit. Tahun lain = read-only (arsip).
    | Bisa di-override via .env: SIKAP_TAHUN_BERJALAN=2027
    |
    */
    'tahun_berjalan' => (int) env('SIKAP_TAHUN_BERJALAN', date('Y')),
];