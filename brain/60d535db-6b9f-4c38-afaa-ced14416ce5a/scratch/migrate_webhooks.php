<?php

use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = DB::table('crm.webhook_logs')->orderBy('id', 'desc')->limit(50)->get();

foreach ($logs as $log) {
    WebhookLog::updateOrCreate(
        ['id' => $log->id],
        [
            'event_type' => $log->event_type,
            'category' => $log->category,
            'from_number' => $log->from_number,
            'message_id' => $log->message_id,
            'payload' => json_decode($log->payload, true),
            'processed' => $log->processed,
            'error_message' => $log->error_message,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at,
            'updated_at' => $log->created_at,
        ]
    );
}

echo "Migrated " . count($logs) . " webhook logs.\n";
