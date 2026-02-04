<?php

namespace App\Modules\Identity\Http\Controllers\Api\V2;

use App\Mail\PasswordResetMail;
use App\Modules\Identity\Domain\Models\User;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController
{
    private const TOKEN_EXPIRY_MINUTES = 60;

    /**
     * Send password reset link to user's email
     * Called from admin panel (employees/users management)
     */
    public function sendResetLink(User $user): JsonResponse
    {
        if (!$user->email) {
            return ApiResponse::badRequest('User tidak memiliki email.');
        }

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        // Generate token
        $token = Str::random(64);

        // Store token
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Generate reset URL (frontend URL)
        $resetUrl = config('app.frontend_url', 'https://app.shineeducationbali.com')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($user->email);

        // Send email
        Mail::to($user->email)->send(
            new PasswordResetMail(
                userName: $user->name,
                resetUrl: $resetUrl,
                expiresInMinutes: self::TOKEN_EXPIRY_MINUTES
            )
        );

        // Log activity
        activity('password-reset')
            ->event('send_reset_link')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['email' => $user->email])
            ->log('Password reset link sent');

        return ApiResponse::ok(null, 'Link reset password berhasil dikirim ke ' . $user->email);
    }

    /**
     * Validate reset token
     */
    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return ApiResponse::badRequest('Token reset password tidak valid.');
        }

        // Check if token matches
        if (!Hash::check($request->token, $record->token)) {
            return ApiResponse::badRequest('Token reset password tidak valid.');
        }

        // Check if token is expired
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(self::TOKEN_EXPIRY_MINUTES)->isPast()) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            return ApiResponse::badRequest('Token reset password sudah kedaluwarsa. Silakan minta link baru.');
        }

        return ApiResponse::ok([
            'valid' => true,
            'email' => $request->email,
        ], 'Token valid.');
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // at least one lowercase letter
                'regex:/[A-Z]/',      // at least one uppercase letter
                'regex:/[0-9]/',      // at least one digit
                'regex:/[@$!%*#?&]/', // at least one special character
            ],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus (@$!%*#?&).',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return ApiResponse::badRequest('Token reset password tidak valid.');
        }

        // Check if token matches
        if (!Hash::check($request->token, $record->token)) {
            return ApiResponse::badRequest('Token reset password tidak valid.');
        }

        // Check if token is expired
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(self::TOKEN_EXPIRY_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            return ApiResponse::badRequest('Token reset password sudah kedaluwarsa. Silakan minta link baru.');
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ApiResponse::badRequest('User tidak ditemukan.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete used token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Log activity
        activity('password-reset')
            ->event('password_changed')
            ->performedOn($user)
            ->log('Password reset successfully');

        return ApiResponse::ok(null, 'Password berhasil diubah. Silakan login dengan password baru.');
    }
}
