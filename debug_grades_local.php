<?php

use App\Modules\Assessment\Domain\Models\AssessmentGrade;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Querying Grades...\n";
    $grades = AssessmentGrade::query()
        ->with(['enrollment.student', 'enrollment.program', 'certificateTemplate', 'teacher.user'])
        ->limit(5)
        ->get();

    echo "Found " . $grades->count() . " grades.\n";

    foreach ($grades as $grade) {
        echo "Grade ID: " . $grade->id . "\n";
        echo "Student: " . ($grade->enrollment->student->nama_lengkap ?? 'NULL') . "\n";
        echo "Teacher: " . ($grade->teacher->user->name ?? 'NULL') . "\n";
    }
    echo "Success.\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
