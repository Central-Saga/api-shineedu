<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

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
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return ApiResponse::unauthorized('Email atau password tidak valid.');
        }

        $user = Auth::user();

        // Check if user is active
        if ($user->status !== 'Aktif') {
            Auth::logout();
            return ApiResponse::forbidden('Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Load relations
        $user->load(['anggota', 'roles.permissions']);

        // Create token with 1 day expiration
        $token = $user->createToken('auth-token', ['*'], now()->addDay());

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
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::ok(null, 'Logout berhasil');
    }
}
