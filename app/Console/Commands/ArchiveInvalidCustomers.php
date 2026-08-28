<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class ArchiveInvalidCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:archive-invalid 
                            {--id= : Periksa/arsipkan spesifik customer ID}
                            {--min-digits=10 : Minimal digit nomor WhatsApp (default: 10)}
                            {--strict : Mode ketat (khusus nomor Indonesia harus diawali 628 dan min 11 digit)}
                            {--all : Sertakan juga customer yang sudah berstatus arsip}
                            {--dry-run : Hanya tampilkan data tanpa melakukan arsip/soft delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete / arsipkan data customer dengan nomor WhatsApp tidak valid atau terlalu pendek (indikasi manipulasi KPI CS)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specificId = $this->option('id');
        $minDigits = (int) $this->option('min-digits');
        $isStrict = $this->option('strict');
        $includeAll = $this->option('all');
        $isDryRun = $this->option('dry-run');

        $this->info("=== PEMERIKSAAN NOMOR CUSTOMER TIDAK VALID ===");

        // If specific ID is requested for diagnostic
        if ($specificId) {
            $customer = Customer::find($specificId);
            if (!$customer) {
                $this->error("Customer dengan ID {$specificId} tidak ditemukan di database ini.");
                return 1;
            }

            $this->info("Data Customer ID: {$customer->id}");
            $this->line("- Nama          : " . ($customer->name ?: '(Tanpa Nama)'));
            $this->line("- Nomor WA      : " . $customer->wa_number);
            $this->line("- Panjang Digit : " . strlen(trim($customer->wa_number)));
            $this->line("- Status Arsip  : " . ($customer->is_archived ? 'DIARSIPKAN (is_archived = 1)' : 'AKTIF (is_archived = 0)'));
            $this->line("- CS Ditugaskan : " . ($customer->assignedUser ? $customer->assignedUser->name : '-'));
            $this->line("- Source        : " . ($customer->source ?: 'Unknown'));
            $this->line("- Dibuat Pada   : " . ($customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : '-'));

            if (!$isDryRun && !$customer->is_archived) {
                $customer->is_archived = 1;
                $customer->save();
                $this->info("=> Customer ID {$specificId} berhasil diarsipkan (soft delete).");
            }
            return 0;
        }

        $query = Customer::query();

        if (!$includeAll) {
            $query->where(function($q) {
                $q->where('is_archived', 0)
                  ->orWhereNull('is_archived')
                  ->orWhere('is_archived', false);
            });
        }

        $query->where(function ($q) use ($minDigits, $isStrict) {
            // Nomor pendek atau nomor dummy umum
            $q->whereRaw("LENGTH(TRIM(wa_number)) < ?", [$minDigits])
              ->orWhereIn('wa_number', ['0', '62', '620', '6200', '62000', '62123', '621234', '6212345'])
              ->orWhere('wa_number', 'LIKE', '620%')
              ->orWhere('wa_number', 'LIKE', '621%')
              ->orWhere('wa_number', 'LIKE', '627%');

            if ($isStrict) {
                // Khusus nomor 62 harus 628 dan minimal 11 digit
                $q->orWhere(function ($sq) {
                    $sq->where('wa_number', 'LIKE', '62%')
                       ->where('wa_number', 'NOT LIKE', '628%');
                })->orWhere(function ($sq2) {
                    $sq2->where('wa_number', 'LIKE', '628%')
                        ->whereRaw("LENGTH(TRIM(wa_number)) < 11");
                });
            }
        });

        $invalidCustomers = $query->orderBy('id', 'desc')->get();

        if ($invalidCustomers->isEmpty()) {
            $this->info("Tidak ditemukan data customer aktif dengan nomor WhatsApp tidak valid.");
            $this->comment("Tips: Jika ingin memeriksa data termasuk yang sudah diarsip, gunakan: php artisan customers:archive-invalid --all");
            return 0;
        }

        $this->table(
            ['ID', 'Nama', 'Nomor WA', 'Panjang', 'Status', 'Source', 'CS Ditugaskan', 'Dibuat Pada'],
            $invalidCustomers->map(function ($c) {
                return [
                    $c->id,
                    $c->name ?: '(Tanpa Nama)',
                    $c->wa_number,
                    strlen(trim($c->wa_number)) . ' digit',
                    $c->is_archived ? 'Sudah Arsip' : 'Aktif',
                    $c->source ?: 'Unknown',
                    $c->assignedUser ? $c->assignedUser->name : '-',
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : '-',
                ];
            })
        );

        $activeCount = $invalidCustomers->filter(function($c) {
            return empty($c->is_archived);
        })->count();
        $totalFound = $invalidCustomers->count();

        $this->warn("Ditemukan {$totalFound} data customer (Aktif: {$activeCount}, Sudah Diarsipkan: " . ($totalFound - $activeCount) . ").");

        if ($isDryRun) {
            $this->info("Mode --dry-run aktif. Tidak ada perubahan yang disimpan ke database.");
            return 0;
        }

        if ($activeCount > 0) {
            $idsToArchive = $invalidCustomers->filter(function($c) {
                return empty($c->is_archived);
            })->pluck('id');

            Customer::whereIn('id', $idsToArchive)->update([
                'is_archived' => 1,
                'updated_at' => now(),
            ]);

            $this->info("Berhasil mengarsipkan (soft delete) {$activeCount} data customer aktif.");
        } else {
            $this->info("Semua data yang ditemukan sudah berstatus arsip sebelumnya.");
        }

        return 0;
    }
}
