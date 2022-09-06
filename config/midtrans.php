<?php

return [
    'serverKey' => env('MIDTRANS_SERVER_KEY', null),
    'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),
    'isSanitized' => env('MIDTRANS_SANITIZED', true),
    'is3ds' => env('MIDTRANS_IS_3DS', true),
];
// setelah selesai bikin jalankan php artisan config:clear

//cara pakai 
// config.midtarns.serverKey
?>