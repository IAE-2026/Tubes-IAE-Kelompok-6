<?php

use App\Http\Middleware\CorsHeaders;
use App\Http\Middleware\ResolveIaeIdentity;

/*
|--------------------------------------------------------------------------
| Konfigurasi Lighthouse (hanya key yang dikustomisasi)
|--------------------------------------------------------------------------
| LighthouseServiceProvider melakukan mergeConfigFrom, sehingga key yang
| tidak didefinisikan di sini otomatis memakai default bawaan paket.
| Kita hanya menimpa: route (+middleware SSO), lokasi schema, guards,
| namespaces resolver, security, dan flag debug.
*/

return [

    'route' => [
        'uri' => '/graphql',
        'name' => 'graphql',
        // Modul 1: ResolveIaeIdentity memverifikasi Bearer JWT & memetakan role
        // lokal pada SETIAP request GraphQL (non-blocking untuk query publik).
        'middleware' => [
            CorsHeaders::class,
            \Nuwave\Lighthouse\Http\Middleware\AcceptJson::class,
            ResolveIaeIdentity::class,
        ],
    ],

    'guards' => ['web'],

    'schema_path' => base_path('graphql/schema.graphql'),

    'schema_cache' => [
        'enable' => env('LIGHTHOUSE_SCHEMA_CACHE_ENABLE', false),
        'path' => env('LIGHTHOUSE_SCHEMA_CACHE_PATH', base_path('bootstrap/cache/lighthouse-schema.php')),
    ],

    'namespaces' => [
        'models' => ['App', 'App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => ['App\\GraphQL\\Directives'],
        'validators' => ['App\\GraphQL\\Validators'],
    ],

    'debug' => env('APP_DEBUG', false)
        ? \GraphQL\Error\DebugFlag::INCLUDE_DEBUG_MESSAGE | \GraphQL\Error\DebugFlag::INCLUDE_TRACE
        : \GraphQL\Error\DebugFlag::NONE,

];
