<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class InvestigateTodayUsers extends Command
{
    protected $signature = 'users:investigate-today';
    protected $description = 'List users created today (2026-02-06) for potential cleanup';

    public function handle()
    {
        $today = '2026-02-06';

        $this->info("Investigating users created on: $today");

        $users = User::whereDate('created_at', $today)->get();

        if ($users->isEmpty()) {
            $this->info("No users found created on $today.");
            return;
        }

        $this->info("Found {$users->count()} users:");

        $headers = ['ID', 'Name', 'Email', 'Created At'];
        $data = $users->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                $user->created_at->toDateTimeString(),
            ];
        });

        $this->table($headers, $data);
    }
}
