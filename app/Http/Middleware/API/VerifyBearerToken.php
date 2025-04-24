<?php

namespace App\Http\Middleware\API;

use App\Enums\ApiErrorCode;
use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBearerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();
        $bearerTokens = config('bearerTokens');

        $tokenWithoutPrefix = trim(str_replace('Bearer', '', $bearerToken));

        if (!in_array($tokenWithoutPrefix, $bearerTokens)) {
            throw new ApiException(ApiErrorCode::INVALID_TOKEN, 'Invalid Bearer Token', 401);
//            return response()->json(['message' => 'Invalid Bearer Token'], 401);
        }

        return $next($request);
    }
}
