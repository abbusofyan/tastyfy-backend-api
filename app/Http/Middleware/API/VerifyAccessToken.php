<?php

namespace App\Http\Middleware\API;

use App\Enums\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Exceptions\TokenExpiredException;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class VerifyAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @throws ApiException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->header('X-Taste-Asia-Access-Token');

        if (!$accessToken) {
            throw new ApiException(ApiErrorCode::TOKEN_MISSING, 'Access Token Missing!', 403);
        }

        $verifiedToken = Sanctum::$personalAccessTokenModel::findToken($accessToken);

        if (!$verifiedToken) {
            throw new ApiException(ApiErrorCode::INVALID_TOKEN, 'Invalid token!', 401);
        }

        if ($verifiedToken->name !== 'auth_token') {
            throw new ApiException(ApiErrorCode::TOKEN_TYPE, 'Invalid Token type!', 401);
        }

        if ($verifiedToken->expires_at && $verifiedToken->expires_at->copy()->tz('UTC') < Carbon::now()) {
            throw new TokenExpiredException();
        }

        $user = $verifiedToken->tokenable;
        Auth::guard('sanctum')->setUser($user);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
