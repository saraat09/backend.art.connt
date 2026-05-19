<?php

return [

    'name'  => env('APP_NAME', 'ArtisanConnect'),
    'env'   => env('APP_ENV',  'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url'   => env('APP_URL',  'http://localhost'),
    'timezone'  => env('APP_TIMEZONE', 'Africa/Casablanca'),
    'locale'    => env('APP_LOCALE', 'fr'),
    'fallback_locale' => 'en',
    'faker_locale'    => 'fr_FR',
    'cipher'    => 'AES-256-CBC',
    'key'       => env('APP_KEY'),
    'previous_keys' => array_filter(
        explode(',', env('APP_PREVIOUS_KEYS', ''))
    ),
    'maintenance' => ['driver' => 'file'],

    'providers' => [
        
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        App\Providers\AppServiceProvider::class,
        Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
    ],

    'aliases' => Illuminate\Support\Facades\Facade::defaultAliases()->merge([
        'JWTAuth'  => Tymon\JWTAuth\Facades\JWTAuth::class,
        'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class,
    ])->toArray(),

];
