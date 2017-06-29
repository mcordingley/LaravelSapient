<?php

return [
    'shared'  => [
        'authentication_key' => env('SAPIENT_SHARED_AUTHENTICATION_KEY', ''),
        'encryption_key'     => env('SAPIENT_SHARED_ENCRYPTION_KEY', ''),
    ],
    'sealing' => [
        'private_key' => env('SAPIENT_SEALING_PRIVATE_KEY', ''),
        'public_key'  => env('SAPIENT_SEALING_PUBLIC_KEY', ''),
    ],
    'signing' => [
        'private_key' => env('SAPIENT_SIGNING_PRIVATE_KEY', ''),
        'public_key'  => env('SAPIENT_SIGNING_PUBLIC_KEY', ''),
    ],
];
