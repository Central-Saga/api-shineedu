<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class ListRoles extends Command
{
    protected $signature = 'debug:list-roles';
    protected $description = 'List all roles';

    public function handle()
    {
        $roles = Role::all();
        $this->table(['ID', 'Name'], $roles->map(fn($r) => [$r->id, $r->name]));
    }
}
