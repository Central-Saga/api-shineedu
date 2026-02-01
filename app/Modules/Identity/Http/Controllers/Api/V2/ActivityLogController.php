<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Shared\Http\Responses\ApiResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('causer_id')) {
            $query->where('causer_id', $request->input('causer_id'));
        }

        if ($request->has('subject_type')) {
            $query->where('subject_type', 'like', "%" . $request->input('subject_type') . "%");
        }

        $logs = $query->paginate($request->input('per_page', 20));

        return ApiResponse::ok($logs);
    }

    public function show($id)
    {
        $log = Activity::with(['causer', 'subject'])->findOrFail($id);
        return ApiResponse::ok($log);
    }
}
