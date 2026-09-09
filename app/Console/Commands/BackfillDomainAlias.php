<?php

namespace App\Console\Commands;

use App\Models\Domain;
use Illuminate\Console\Command;

class BackfillDomainAlias extends Command
{
    protected $signature = 'domains:backfill-alias';

    protected $description = 'Isi alias untuk domain yang aliasnya masih kosong';

    public function handle(): int
    {
        $kosong = Domain::whereNull('alias')->orWhere('alias', '')->get();

        if ($kosong->isEmpty()) {
            $this->info('Semua domain sudah punya alias.');

            return self::SUCCESS;
        }

        foreach ($kosong as $domain) {
            $domain->update(['alias' => Domain::generateAlias()]);
            $this->line("  {$domain->domain} -> {$domain->alias}");
        }

        $this->info("Selesai: {$kosong->count()} domain diisi aliasnya.");

        return self::SUCCESS;
    }
}
