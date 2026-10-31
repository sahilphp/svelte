<?php

declare(strict_types=1);

/**
 * Svelte frontend configuration.
 *
 * Development and production differ only in how assets resolve:
 *
 *  - development: the Vite dev server (and its HMR client) is loaded;
 *  - production: `public/build/.vite/manifest.json` is read and every
 *    referenced file is verified.
 *
 * `public/hot` may override `dev_server` when the Vite server had to use a
 * different port. Relative paths are resolved against the project root.
 */
$environment = env('APP_ENV', 'development');

$development = env('FRONTEND_DEVELOPMENT');

if (!is_bool($development)) {
    $development = $environment !== 'production';
}

return [
    'mount_id' => 'app',
    'title_key' => 'title',
    'lang' => env('APP_LOCALE', 'en'),
    'default_title' => env('APP_NAME', 'SahilPHP'),
    'development' => $development,
    'dev_server' => env('VITE_DEV_SERVER', 'http://127.0.0.1:5173'),
    'build_path' => 'public/build',
    'build_public_path' => 'build',
    'manifest' => '.vite/manifest.json',
    'entry' => 'src/main.ts',
    'verify_assets' => true,
    'pages_directory' => 'src/pages',
];
