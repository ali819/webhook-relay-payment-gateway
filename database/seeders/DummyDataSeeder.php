<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\WebhookLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data dummy untuk menguji panel (DataTables, paging, filter, retry).
 *
 *   php artisan db:seed --class=DummyDataSeeder
 *
 * Opsi lewat environment variable:
 *   DUMMY_RESET=1     hapus dulu semua domain & log yang ada
 *   DUMMY_DOMAINS=30  jumlah domain (default 24)
 *   DUMMY_LOGS=5000   jumlah log   (default 3000)
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('DummyDataSeeder tidak boleh jalan di production.');

            return;
        }

        $domainCount = (int) env('DUMMY_DOMAINS', 24);
        $logCount    = (int) env('DUMMY_LOGS', 3000);

        if (env('DUMMY_RESET')) {
            // Log dulu, baru domain — webhook_logs punya FK ke domains.
            WebhookLog::query()->delete();
            Domain::query()->delete();
            $this->command->warn('Domain & log lama dihapus (DUMMY_RESET aktif).');
        }

        $domains = $this->seedDomains($domainCount);
        $this->seedLogs($domains, $logCount);

        $this->command->info("Selesai: {$domains->count()} domain, {$logCount} log dummy.");
        $this->command->line('Total sekarang — domain: ' . Domain::count() . ', log: ' . WebhookLog::count());
    }

    /**
     * Domain dibuat per provider agar tiap filter di panel ada isinya,
     * termasuk beberapa yang nonaktif.
     */
    private function seedDomains(int $count)
    {
        $perProvider = max(1, intdiv($count, count(Domain::PROVIDERS)));

        foreach (Domain::PROVIDERS as $provider) {
            Domain::factory()
                ->count($perProvider)
                ->provider($provider)
                ->create();

            Domain::factory()->provider($provider)->inactive()->create();
        }

        return Domain::all();
    }

    /**
     * Log disisipkan per batch 500 baris — jauh lebih cepat daripada
     * satu INSERT per model saat jumlahnya ribuan.
     */
    private function seedLogs($domains, int $count): void
    {
        if ($domains->isEmpty()) {
            $this->command->warn('Tidak ada domain, log dilewati.');

            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        foreach (array_chunk(range(1, $count), 500) as $chunk) {
            $rows = [];

            foreach ($chunk as $ignored) {
                $row = WebhookLog::factory()
                    ->forDomain($domains->random())
                    ->make()
                    ->getAttributes();

                $row['payload'] = is_array($row['payload']) ? json_encode($row['payload']) : $row['payload'];
                $rows[] = $row;
            }

            DB::table('webhook_logs')->insert($rows);
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->command->newLine();
    }
}
