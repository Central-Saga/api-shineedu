<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Identity\Domain\Models\User;
use Spatie\Permission\Models\Role;

class AssignRoleToUser extends Command
{
    protected $signature = 'users:assign-role {email} {role}';
    protected $description = 'Assign a role to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User not found: $email");
            return;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role not found: $roleName");
            return;
        }

        $user->assignRole($role);
        $this->info("Assigned role '$roleName' to user '{$user->name}'");

        $this->info("Current roles: " . $user->getRoleNames()->implode(', '));
    }
}
