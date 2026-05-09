<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BackupDatabaseToTelegram extends Command
{
    protected $signature = 'db:backup-telegram {--token=} {--chat_id=}';
    protected $description = 'Backup database and send to Telegram';

    public function handle()
    {
        $token = $this->option('token') ?: Setting::get('telegram_bot_token');
        $chatId = $this->option('chat_id') ?: Setting::get('telegram_chat_id');
        $enabled = Setting::get('backup_enabled');

        if (!$token || !$chatId) {
            $this->error('Telegram Token or Chat ID not configured.');
            return 1;
        }

        // If called from scheduler (no explicit token), check if enabled
        if (!$this->option('token') && !$enabled) {
            $this->info('Backup is disabled in settings.');
            return 0;
        }

        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $filename = "crm_backup-" . $dbName . "-" . date('Y-m-d_H-i-s') . ".sql";
        $path = storage_path("app/backups/" . $filename);

        if (!is_dir(storage_path("app/backups"))) {
            mkdir(storage_path("app/backups"), 0755, true);
        }

        $passPart = $dbPass ? '--password=' . escapeshellarg($dbPass) : '';
        $mysqldump = $this->getMysqldumpPath();

        // Construct command - Note: mysqldump must be in PATH
        $command = sprintf(
            '%s --user=%s %s --host=%s %s > %s 2>&1',
            $mysqldump,
            escapeshellarg($dbUser),
            $passPart,
            escapeshellarg($dbHost),
            escapeshellarg($dbName),
            escapeshellarg($path)
        );
        
        $this->info("Dumping database using: " . $mysqldump);
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorLog = "";
            if (file_exists($path)) {
                $errorLog = file_get_contents($path);
                unlink($path); // Delete the "backup" file which actually contains the error
            }
            
            $this->error("Database dump failed with code {$returnVar}.");
            if ($errorLog) {
                $this->error("MySQL Error: " . $errorLog);
            }
            Log::error("Backup Failed: Database dump failed (Code: {$returnVar}). Output: " . $errorLog);
            return 1;
        }

        if (!file_exists($path) || filesize($path) == 0) {
            $this->error("Backup file is empty or missing.");
            return 1;
        }

        $this->info("Sending to Telegram...");
        
        try {
            $response = Http::attach(
                'document', file_get_contents($path), $filename
            )->post("https://api.telegram.org/bot{$token}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => "🛡️ Daily Backup CRM\n📅 Date: " . date('d M Y H:i:s') . "\n💾 Database: " . $dbName
            ]);

            if ($response->successful()) {
                $this->info("Backup sent successfully!");
                unlink($path);
                return 0;
            } else {
                $this->error("Failed to send backup: " . $response->body());
                Log::error("Backup Failed: " . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Backup Exception: " . $e->getMessage());
            return 1;
        }
    }

    protected function getMysqldumpPath()
    {
        $mysqldump = 'mysqldump';
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Check Laragon default paths
            $laragonBase = null;
            if (is_dir('D:\laragon\bin\mysql')) $laragonBase = 'D:\laragon\bin\mysql';
            elseif (is_dir('C:\laragon\bin\mysql')) $laragonBase = 'C:\laragon\bin\mysql';
            
            if ($laragonBase) {
                $dirs = array_filter(glob($laragonBase . '\mysql-*'), 'is_dir');
                if (!empty($dirs)) {
                    // Get the latest/first one
                    $latestDir = end($dirs);
                    $path = $latestDir . '\bin\mysqldump.exe';
                    if (file_exists($path)) {
                        return '"' . $path . '"';
                    }
                }
            }
        }
        
        return $mysqldump;
    }
}
