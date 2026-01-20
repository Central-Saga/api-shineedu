<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

use App\Modules\Identity\Http\Resources\PermissionResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController
{
    /**
     * Display a listing of all permissions.
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('name')
            ->get();

        return ApiResponse::ok(
            PermissionResource::collection($permissions),
            'Data permission berhasil diambil'
        );
    }
}
