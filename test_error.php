<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();
\Illuminate\Support\Facades\Auth::loginUsingId(1);

$request = Illuminate\Http\Request::create('/roles', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() == 500) {
    echo $response->getContent();
}
