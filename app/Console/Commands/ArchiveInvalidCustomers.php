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
        $isDryRun = $this->option('dry-run');

        $this->info("Memeriksa customer dengan nomor WhatsApp < {$minDigits} digit atau nomor dummy...");

        $invalidCustomers = Customer::where('is_archived', 0)
            ->where(function($q) use ($minDigits) {
                $q->whereRaw("LENGTH(wa_number) < ?", [$minDigits])
                  ->orWhereIn('wa_number', ['0', '62', '620', '6200', '62000', '62123', '621234', '6212345']);
            })
            ->get();

        if ($invalidCustomers->isEmpty()) {
            $this->info("Tidak ditemukan data customer aktif dengan nomor WhatsApp tidak valid.");
            return 0;
        }

        $this->table(
            ['ID', 'Nama', 'Nomor WA', 'Panjang Digit', 'Source', 'Dibuat Pada'],
            $invalidCustomers->map(function ($c) {
                return [
                    $c->id,
                    $c->name ?: '(Tanpa Nama)',
                    $c->wa_number,
                    strlen($c->wa_number),
                    $c->source ?: 'Unknown',
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : '-',
                ];
            })
        );

        $count = $invalidCustomers->count();
        $this->warn("Ditemukan {$count} data customer tidak valid.");

        if ($isDryRun) {
            $this->info("Mode --dry-run aktif. Tidak ada perubahan yang disimpan ke database.");
            return 0;
        }

        $ids = $invalidCustomers->pluck('id');
        Customer::whereIn('id', $ids)->update([
            'is_archived' => 1,
            'updated_at' => now(),
        ]);

        $this->info("Berhasil mengarsipkan (soft delete) {$count} data customer.");
        return 0;
    }
}
