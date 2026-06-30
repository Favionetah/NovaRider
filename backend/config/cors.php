<?php

return [
    'paths' => ['*'],
    'allowed_methods' => ['*'],
<<<<<<< HEAD
'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173'],    'allowed_origins_patterns' => [],
=======
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_origins_patterns' => [],
>>>>>>> respaldo-caja
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
