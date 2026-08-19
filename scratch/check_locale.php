<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(2);
echo "User 2 Locale: " . ($user ? ($user->locale ?? 'null') : 'not found') . "\n";
echo "Active Global Locale: " . \Illuminate\Support\Facades\Cache::get('app.current_locale', 'not set') . "\n";
