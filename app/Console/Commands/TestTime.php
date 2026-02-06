<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class TestTime extends Command
{
    protected $signature = 'test:time';
    protected $description = 'Debug server time and Carbon settings';

    public function handle()
    {
        $this->info("PHP Timezone: " . date_default_timezone_get());
        $this->info("App Config Timezone: " . config('app.timezone'));
        $this->info("Now (Carbon): " . Carbon::now()->toDateTimeString());
        $this->info("Now (Native): " . date('Y-m-d H:i:s'));

        $today = Carbon::now()->toDateString();
        $this->info("Today: " . $today);

        $checkoutTime = '20:20:00';
        $currentTime = Carbon::now()->format('H:i:s');

        $this->info("Check: Is {$currentTime} < {$checkoutTime}?");
        if ($currentTime < $checkoutTime) {
            $this->info("✅ YES (Would SKIP auto-checkout for today)");
        } else {
            $this->error("❌ NO (Would EXECUTE auto-checkout for today)");
        }
    }
}
