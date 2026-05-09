<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MigrateLegacyData extends Command
{
    protected $signature = 'db:migrate-legacy {file} {--merge : Merge data without truncating existing tables}';
    protected $description = 'Migrate data from legacy SQL file using a staging database with optional merge support';

    public function handle()
    {
        $file = $this->argument('file');
        $isMerge = $this->option('merge');
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $stagingDb = 'staging_legacy_migration';
        
        $this->info("1. Creating staging database: {$stagingDb}");
        DB::statement("DROP DATABASE IF EXISTS `{$stagingDb}`");
        DB::statement("CREATE DATABASE `{$stagingDb}`");

        $this->info("2. Importing SQL to staging database...");
        $this->importSqlToStaging($file, $stagingDb);

        // Configure dynamic connection for staging
        config(['database.connections.staging' => array_merge(config('database.connections.mysql'), [
            'database' => $stagingDb,
        ])]);

        $this->info("3. Disabling foreign key checks...");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        if (!$isMerge) {
            $this->info("4. Truncating current tables (Clean Mode)...");
            $this->truncateTables();
        } else {
            $this->info("4. Skipping truncation (Merge Mode)...");
        }

        $this->info("5. Migrating data...");
        $this->migrateEntities($isMerge);

        $this->info("6. Resetting user passwords...");
        $this->resetPasswords();

        $this->info("7. Enabling foreign key checks...");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("8. Staging database kept for verification: {$stagingDb}");
        // DB::statement("DROP DATABASE IF EXISTS `{$stagingDb}`");

        $this->info("Migration completed successfully!");
        return 0;
    }

    protected function importSqlToStaging($file, $dbName)
    {
        $dbHost = config('database.connections.mysql.host');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $passPart = $dbPass ? '--password=' . escapeshellarg($dbPass) : '';

        $command = sprintf(
            'mysql --user=%s %s --host=%s %s < %s',
            escapeshellarg($dbUser),
            $passPart,
            escapeshellarg($dbHost),
            escapeshellarg($dbName),
            escapeshellarg($file)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Failed to import SQL to staging database. Code: {$returnVar}");
        }
    }

    protected function truncateTables()
    {
        $tables = [
            'users', 'companies', 'customers', 'labels', 'customer_labels',
            'deals', 'deal_stages', 'deal_activities', 'messages',
            'broadcasts', 'broadcast_recipients', 'auto_replies', 'settings',
            'webhook_logs', 'message_revisions', 'wa_groups', 'templates', 'template_categories'
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }

    protected function migrateEntities($isMerge = false)
    {
        $entities = [
            'users', 'roles', 'permissions', 'role_permissions', 'companies',
            'labels', 'deal_stages', 'customers', 'customer_labels', 'deals',
            'deal_activities', 'messages', 'message_revisions', 'broadcasts',
            'broadcast_recipients', 'auto_replies', 'settings', 'wa_groups',
            'webhook_logs', 'template_categories', 'templates'
        ];

        foreach ($entities as $table) {
            try {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    $this->warn("   - Table {$table} does not exist in target. Skipping.");
                    continue;
                }

                $count = DB::connection('staging')->table($table)->count();
                $this->info("   - Migrating table: {$table} ({$count} records)");
                
                if ($count === 0) continue;

                $targetColumns = DB::getSchemaBuilder()->getColumnListing($table);
                $hasId = in_array('id', $targetColumns);

                $query = DB::connection('staging')->table($table);
                
                $processChunk = function ($chunk) use ($table, $targetColumns, $isMerge, $hasId) {
                    $insertData = [];
                    foreach ($chunk as $row) {
                        $rowArray = (array) $row;
                        $cleanRow = [];
                        
                        foreach ($rowArray as $col => $val) {
                            if (in_array($col, $targetColumns)) {
                                if ($val === '0000-00-00' || $val === '0000-00-00 00:00:00') {
                                    $val = null;
                                }

                                if ($table === 'customers' && $col === 'gender') {
                                    $val = strtoupper($val);
                                    if ($val === 'LAKI-LAKI' || $val === 'MALE' || $val === 'L') $val = 'L';
                                    elseif ($val === 'PEREMPUAN' || $val === 'FEMALE' || $val === 'P') $val = 'P';
                                    else $val = null;
                                }

                                if ($val === '') {
                                    $val = null;
                                }

                                $cleanRow[$col] = $val;
                            }
                        }
                        
                        $insertData[] = $cleanRow;
                    }

                    if (!empty($insertData)) {
                        try {
                            if ($isMerge) {
                                // In merge mode, skip duplicates to preserve new data
                                DB::table($table)->insertOrIgnore($insertData);
                            } else {
                                DB::table($table)->insert($insertData);
                            }
                        } catch (\Exception $e) {
                            $this->warn("     Chunk failed for {$table}, trying one by one...");
                            foreach ($insertData as $data) {
                                try {
                                    DB::table($table)->insertOrIgnore($data);
                                } catch (\Exception $e2) {
                                    Log::error("Failed to migrate row in {$table}: " . $e2->getMessage());
                                }
                            }
                        }
                    }
                };

                if ($hasId) {
                    $query->orderBy('id')->chunk(500, $processChunk);
                } else {
                    $query->get()->chunk(500)->each($processChunk);
                }
            } catch (\Exception $e) {
                $this->error("     Error migrating {$table}: " . $e->getMessage());
            }
        }
    }

    protected function resetPasswords()
    {
        DB::table('users')->update([
            'password' => Hash::make('password')
        ]);
    }
}
