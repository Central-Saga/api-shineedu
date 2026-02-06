<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Identity\Domain\Models\User;

class CheckUserRoles extends Command
{
    protected $signature = 'debug:user-roles {name}';
    protected $description = 'Check roles for a user';

    public function handle()
    {
        $name = $this->argument('name');
        $user = User::where('name', 'like', "%$name%")->first();

        if (!$user) {
            $this->error("User not found matching: $name");
            return;
        }

        $this->info("User: {$user->id} - {$user->name} ({$user->email})");
        $this->info("Roles: " . $user->getRoleNames()->implode(', '));
    }
}
