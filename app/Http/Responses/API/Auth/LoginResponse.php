<?php

namespace App\Http\Responses\API\Auth;

use App\Http\Responses\API\BaseResponse;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @OA\Schema(
 *     schema="LoginResponse",
 *     title="Login Response",
 *     description="Response structure for successful login",
 *     type="object",
 *     required={"id", "access_token", "access_token_expires_at", "refresh_token", "refresh_token_expires_at"},
 *     properties={
 *          @OA\Property(property="id", type="integer", readOnly=true, description="User ID"),
 *          @OA\Property(property="name", type="string", description="User name"),
 *          @OA\Property(property="access_token", type="string", description="Access Token"),
 *          @OA\Property(property="access_token_expires_at", type="integer", description="Timestamp of access token expiration"),
 *          @OA\Property(property="refresh_token", type="string", description="Refresh Token"),
 *          @OA\Property(property="refresh_token_expires_at", type="integer", description="Timestamp of refresh token expiration"),
 *          @OA\Property(property="customer_id", type="string", nullable=true, description="Customer ID (if the user is a customer)"),
 *          @OA\Property(property="role", type="string", nullable=true, description="Customer role (if the user is a customer)"),
 *          @OA\Property(property="permissions", type="array",
 *              @OA\Items(type="string", description="Permission names (if the user is a customer)"),
 *              nullable=true
 *          ),
 *          @OA\Property(property="credit_balance", type="number", format="float", nullable=true, description="Credit balance (if the user has the 'have credit' permission)"),
 *          @OA\Property(property="cash_balance", type="number", format="float", nullable=true, description="Cash balance (if the user has the 'have cash' permission)"),
 *          @OA\Property(property="cash_split", type="number", format="float", nullable=true, description="Cash split (if the user has the 'have cash' permission)"),
 *          @OA\Property(property="credit_split", type="number", format="float", nullable=true, description="Credit split (if the user has the 'have cash' permission)"),
 *          @OA\Property(property="is_first_login", type="boolean", description="to identify if the user is first login or not (using 1 or 0)"),
 *     }
 * )
 */
class LoginResponse extends BaseResponse
{
    public function __construct(User $user, $accessToken, $refreshToken, $message = 'Login successful')
    {
        $accessTokenData = PersonalAccessToken::findToken($accessToken);
        $refreshTokenData = PersonalAccessToken::findToken($refreshToken);


        $userData = [
            'id'                       => $user->id,
            'name'                     => $user->name,
            'customer_id'              => $user->customer->customer_id,
            'role'                     => $user->customer->role_name,
            'is_first_login'           => $user->is_first_login,
            'permissions'              => $user->customer->roles->first()->permissions->pluck('name')->toArray(),
            'access_token'             => $accessToken,
            'access_token_expires_at'  => $accessTokenData?->expires_at->timestamp ?? null,
            'refresh_token'            => $refreshToken,
            'refresh_token_expires_at' => $refreshTokenData?->expires_at->timestamp ?? null,
        ];

        if ($user->customer->roles()->first()->hasPermissionTo('have credit')) {
            $userData = array_merge($userData, [
                'credit_balance' => $user->customer->credit_balance,
            ]);
        }

        if ($user->customer->roles()->first()->hasPermissionTo('have cash')) {
            $userData = array_merge($userData, [
                'cash_balance' => $user->customer->cash_balance,
                'cash_split'   => $user->customer->cash_split,
                'credit_split' => $user->customer->credit_split,
            ]);
        }

        parent::__construct($userData, $message);
    }
}
