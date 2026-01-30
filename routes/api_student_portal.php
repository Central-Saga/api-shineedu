<?php

use Illuminate\Support\Facades\Route;

// Student Portal Routes
Route::middleware(['auth:sanctum'])->prefix('v2')->group(function () {
    Route::get('/murid/my-materi', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'getMyMateri']);
    Route::get('/murid/my-assignments', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'getMyAssignments']);
    Route::post('/murid/materi/{materiAssignmentId}/mark-accessed', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'markMateriAccessed']);
    Route::get('/murid/progress', [\App\Modules\Learning\Http\Controllers\StudentPortalController::class, 'getProgress']);
});
