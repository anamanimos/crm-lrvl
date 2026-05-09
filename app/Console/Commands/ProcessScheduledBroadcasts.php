<?php

namespace App\Console\Commands;

use App\Models\Broadcast;
use App\Jobs\ProcessBroadcastJob;
use Illuminate\Console\Command;

class ProcessScheduledBroadcasts extends Command
{
    protected $signature = 'broadcast:process-scheduled';
    protected $description = 'Process scheduled broadcasts that are due';

    public function handle()
    {
        $broadcasts = Broadcast::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($broadcasts->isEmpty()) {
            return;
        }

        foreach ($broadcasts as $broadcast) {
            $this->info("Starting scheduled broadcast: {$broadcast->name}");
            
            $broadcast->update([
                'status' => 'running',
                'started_at' => now()
            ]);

            ProcessBroadcastJob::dispatch($broadcast);
        }
    }
}
