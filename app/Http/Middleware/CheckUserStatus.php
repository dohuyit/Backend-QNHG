<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->status !== User::STATUS_ACTIVE) {
            Auth::logout();

            return response()->json([
                'message' => 'Tài khoản đã bị vô hiệu hóa',
                'code' => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        return $next($request);
    }
}
