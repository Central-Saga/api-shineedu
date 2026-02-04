<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Modules\HR\Domain\Models\Employee;
use App\Modules\HR\Domain\Models\Cuti;
use App\Modules\Identity\Domain\Models\User;

$users = User::where('name', 'like', '%Wira%')->get();

foreach ($users as $user) {
    echo "Found User: {$user->name} (ID: {$user->id})\n";
    $employee = Employee::where('user_id', $user->id)->first();
    if ($employee) {
        $cutis = Cuti::where('karyawan_id', $employee->id)->get();
        foreach ($cutis as $cuti) {
            echo "Deleting Cuti: ID {$cuti->id}, Type: {$cuti->jenis}, Date: {$cuti->start_date} to {$cuti->end_date}\n";
            $cuti->forceDelete();
        }
    }
}
echo "Done.\n";
