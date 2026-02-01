<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    /**
     * Login user and return token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = $request->input('email');
        $password = $request->input('password');

        $user = null;

        // 1. Try Login by Email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            if (Auth::attempt(['email' => $identifier, 'password' => $password])) {
                $user = Auth::user();
            }
        } else {
            // 2. Try Login by Employee Code
            $employee = \App\Modules\HR\Domain\Models\Employee::where('kode_karyawan', $identifier)->first();
            if ($employee && $employee->user) {
                if (\Illuminate\Support\Facades\Hash::check($password, $employee->user->password)) {
                    $user = $employee->user;
                    Auth::login($user);
                }
            }

            // 3. Try Login by Student Code
            if (!$user) {
                $student = \App\Modules\Student\Domain\Models\Murid::where('kode_murid', $identifier)->first();
                if ($student && $student->user) {
                    if (\Illuminate\Support\Facades\Hash::check($password, $student->user->password)) {
                        $user = $student->user;
                        Auth::login($user);
                    }
                }
            }
        }

        if (!$user) {
            return ApiResponse::unauthorized('Email, Kode Murid, Kode Karyawan atau password tidak valid.');
        }

        // Check if user is active
        if ($user->status !== UserStatus::AKTIF) {
            Auth::logout();
            return ApiResponse::forbidden('Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Load relations
        $user->load(['anggota', 'roles.permissions']);

        // Create token with 1 day expiration
        $token = $user->createToken('auth-token', ['*'], now()->addDay());

        // Log Activity
        activity('auth')
            ->event('login')
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
            ->log('Logged In');

        return ApiResponse::ok([
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
        ], 'Login berhasil');
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['anggota', 'roles.permissions']);

        return ApiResponse::ok(new UserResource($user), 'Data user berhasil diambil');
    }

    /**
     * Logout current user (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        // Log Activity
        activity('auth')
            ->event('logout')
            ->causedBy($request->user())
            ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
            ->log('Logged Out');

        $request->user()->currentAccessToken()->delete();

        return ApiResponse::ok(null, 'Logout berhasil');
    }
}
