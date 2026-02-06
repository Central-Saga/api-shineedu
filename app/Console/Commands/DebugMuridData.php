<?php

namespace App\Console\Commands;

use App\Modules\Student\Domain\Models\Murid;
use Illuminate\Console\Command;

class DebugMuridData extends Command
{
    protected $signature = 'debug:murid-data {kode_murid?}';
    protected $description = 'Debug murid data to check no_hp and no_hp_wali';

    public function handle()
    {
        $kodeMurid = $this->argument('kode_murid');

        if ($kodeMurid) {
            $murid = Murid::where('kode_murid', $kodeMurid)->first();

            if (!$murid) {
                $this->error("Murid dengan kode {$kodeMurid} tidak ditemukan.");
                return 1;
            }

            $this->displayMurid($murid);
        } else {
            $murids = Murid::latest()->limit(5)->get();

            if ($murids->isEmpty()) {
                $this->info('Tidak ada data murid.');
                return 0;
            }

            $this->info("Menampilkan 5 murid terakhir:\n");

            foreach ($murids as $murid) {
                $this->displayMurid($murid);
                $this->line('---');
            }
        }

        return 0;
    }

    private function displayMurid($murid)
    {
        $this->info("ID: {$murid->id}");
        $this->info("Kode Murid: {$murid->kode_murid}");
        $this->info("Nama: {$murid->nama_lengkap}");
        $this->info("No HP Murid: " . ($murid->no_hp ?: '(kosong)'));
        $this->info("No HP Wali: " . ($murid->no_hp_wali ?: '(kosong)'));

        $display = $murid->no_hp ?: $murid->no_hp_wali;
        $this->info("No HP Display: " . ($display ?: '(kosong)'));
    }
}
