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
        $minDigits = (int) $this->option('min-digits');
        $isStrict = $this->option('strict');
        $includeAll = $this->option('all');
        $isDryRun = $this->option('dry-run');

        $this->info("=== PEMERIKSAAN NOMOR CUSTOMER TIDAK VALID ===");
        $this->info("Kriteria: < {$minDigits} digit" . ($isStrict ? " ATAU format Indonesia bukan 628 / < 11 digit" : "") . ($includeAll ? " (Termasuk data yang sudah diarsip)" : " (Hanya data aktif)"));

        $query = Customer::query();

        if (!$includeAll) {
            $query->where('is_archived', 0);
        }

        $query->where(function ($q) use ($minDigits, $isStrict) {
            // Nomor pendek atau nomor dummy umum
            $q->whereRaw("LENGTH(wa_number) < ?", [$minDigits])
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
                        ->whereRaw("LENGTH(wa_number) < 11");
                });
            }
        });

        $invalidCustomers = $query->orderBy('id', 'desc')->get();

        if ($invalidCustomers->isEmpty()) {
            $this->info("Tidak ditemukan data customer dengan nomor WhatsApp tidak valid sesuai kriteria.");
            return 0;
        }

        $this->table(
            ['ID', 'Nama', 'Nomor WA', 'Panjang', 'Status', 'Source', 'CS Ditugaskan', 'Dibuat Pada'],
            $invalidCustomers->map(function ($c) {
                return [
                    $c->id,
                    $c->name ?: '(Tanpa Nama)',
                    $c->wa_number,
                    strlen($c->wa_number) . ' digit',
                    $c->is_archived ? 'Sudah Arsip' : 'Aktif',
                    $c->source ?: 'Unknown',
                    $c->assignedUser ? $c->assignedUser->name : '-',
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : '-',
                ];
            })
        );

        $activeCount = $invalidCustomers->where('is_archived', 0)->count();
        $totalFound = $invalidCustomers->count();

        $this->warn("Ditemukan {$totalFound} data customer (Aktif: {$activeCount}, Sudah Diarsipkan: " . ($totalFound - $activeCount) . ").");

        if ($isDryRun) {
            $this->info("Mode --dry-run aktif. Tidak ada perubahan yang disimpan ke database.");
            return 0;
        }

        if ($activeCount > 0) {
            $idsToArchive = $invalidCustomers->where('is_archived', 0)->pluck('id');
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
