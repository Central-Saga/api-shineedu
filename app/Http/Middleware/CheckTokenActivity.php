<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Shared\Http\Responses\ApiResponse;

class CheckTokenActivity
{
    /**
     * Handle an incoming request.
     *
     * Check if the token has been inactive for more than 15 minutes.
     * If yes, revoke the token and return 401.
     * If no, update last_used_at and continue.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $token = $user->currentAccessToken();
            $lastUsedAt = $token->last_used_at;

            // Define idle timeout in minutes
            $idleTimeoutMinutes = 15;

            // Check if token has been used before and if it's been idle for too long
            if ($lastUsedAt && $lastUsedAt->diffInMinutes(now()) > $idleTimeoutMinutes) {
                // Token has been idle for more than 15 minutes, revoke it
                $token->delete();

                return ApiResponse::unauthorized('Sesi Anda telah berakhir karena tidak ada aktivitas selama 15 menit. Silakan login kembali.');
            }

            // Token is still active, update last_used_at
            // Note: Sanctum automatically updates last_used_at, but we can force it here
            $token->forceFill(['last_used_at' => now()])->save();
        }

        return $next($request);
    }
}
