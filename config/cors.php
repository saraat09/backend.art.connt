 <?php
return [
    'paths' => ['api/*', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

'allowed_origins' => [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'https://frontend-arrt-connt.vercel.app/',
],
    'allowed_headers' => ['*'],

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

    'max_age' => 0,
];