<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

use App\Modules\Identity\Http\Requests\UserStoreRequest;
use App\Modules\Identity\Http\Requests\UserUpdateRequest;
use App\Modules\Identity\Http\Requests\UserUpdateRoleRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use App\Modules\Identity\Models\User;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles.permissions')
            ->orderBy('name')
            ->paginate(15);

        return ApiResponse::paginated(
            UserResource::collection($users),
            $users,
            'Data user berhasil diambil'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);

        // Assign single role
        $role = Role::findByName($validated['role'], 'web');
        $user->syncRoles([$role]);

        $user->load('roles.permissions');

        return ApiResponse::created(
            new UserResource($user),
            'User berhasil dibuat'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $user->load('roles.permissions');

        return ApiResponse::ok(
            new UserResource($user),
            'Data user berhasil diambil'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['status'])) {
            $user->status = $validated['status'];
        }

        // Update password if provided and not empty
        if (isset($validated['password']) && !empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->load('roles.permissions');

        return ApiResponse::ok(
            new UserResource($user),
            'User berhasil diupdate'
        );
    }

    /**
     * Update user role.
     */
    public function updateRole(UserUpdateRoleRequest $request, User $user): JsonResponse
    {
        $role = Role::findByName($request->validated()['role'], 'web');

        // Sync single role (replaces existing roles)
        $user->syncRoles([$role]);

        $user->load('roles.permissions');

        return ApiResponse::ok(
            new UserResource($user),
            'Role user berhasil diupdate'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete user
        $user->delete();

        return ApiResponse::ok(null, 'User berhasil dihapus');
    }
}
