<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$fakes = [
    '2026_01_08_090558_create_permission_tables' => Schema::hasTable('permissions'),
    '2026_01_10_113716_create_personal_access_tokens_table' => Schema::hasTable('personal_access_tokens'),
    '2026_01_30_100000_create_job_vacancies_table' => Schema::hasTable('job_vacancies'),
    '2026_01_30_100001_create_job_applications_table' => Schema::hasTable('job_applications'),
    '2026_01_10_114942_add_status_to_users_table' => Schema::hasColumn('users', 'status'),
    '2026_01_19_005141_add_soft_deletes_to_users_table' => Schema::hasColumn('users', 'deleted_at'),
    '2026_01_30_120000_add_detail_columns_to_job_vacancies_table' => Schema::hasColumn('job_vacancies', 'description'),
];

foreach ($fakes as $migration => $condition) {
    if ($condition) {
        if (!DB::table('migrations')->where('migration', $migration)->exists()) {
            DB::table('migrations')->insert(['migration' => $migration, 'batch' => 1]);
            echo "Faked $migration\n";
        }
    }
}
