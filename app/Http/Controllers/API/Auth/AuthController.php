<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Responses\API\Auth\LoginResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use OpenApi\Attributes as OA;
use App\Http\Responses\API\BaseResponse;
use Illuminate\Support\Facades\Hash;
use Twilio\Rest\Client;


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/m/login",
     *     operationId="loginUser",
     *     summary="User login and send OTP for verification",
     *     tags={"Authentication"},
     *     security={{"Bearer Token" : {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="+6588837454"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully. Please verify to log in.")
     *         )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Error: Unauthorized",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="error", type="object",
     *                  @OA\Property(property="code", type="integer", example=ApiErrorCode::UNAUTHORIZED),
     *                  @OA\Property(property="message", type="string", example="Invalid login credentials"),
     *                  @OA\Property(property="status", type="integer", example=401)
     *              )
     *          ),
     *      ),
     * )
     * @throws ApiException
     */
    public function login(Request $request)
    {

        if (!Auth::attempt($request->only('phone', 'password'))) {
            throw new ApiException(
                ApiErrorCode::UNAUTHORIZED,
                'Invalid login credentials',
                ApiErrorCode::UNAUTHORIZED->value
            );
        }

        $user = $request->user();

        if (!$user->customer) {
            throw new ApiException(
                ApiErrorCode::UNAUTHORIZED,
                'Invalid login credentials',
                ApiErrorCode::UNAUTHORIZED->value
            );
        }

        // Send OTP via Twilio
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        $verification = $twilio->verify->v2
            ->services(env('TWILIO_SERVICE_ID'))
            ->verifications->create(
                $user->phone, // To
                "sms" // Channel
            );

        return new BaseResponse(
            ['success' => true],
            'OTP sent successfully. Please verify to log in. OTP will expire in 10 minutes.',
            200
        );
    }


    /**
     * @OA\Post(
     *     path="/m/verify-login",
     *     operationId="verifyLoginWithOTP",
     *     summary="Verify OTP and log in",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password", "otp"},
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="otp", type="integer", example=123456)
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *     )
     * )
     */
    public function verifyLogin(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'otp' => 'required|numeric',
        ]);

        $user = User::where('phone', $request->input('phone'))->first();

        if (!$user) {
            throw new ApiException(
                ApiErrorCode::UNAUTHORIZED,
                'Invalid phone number',
                ApiErrorCode::UNAUTHORIZED->value
            );
        }

        // code for if user phone is +6581234567 then otp is 123456 and verificationCheck->status is approved
        if ($request->input('phone') === '+6581234567' && $request->input('otp') === '123456') {
            $accessToken = $user->createToken('auth_token', ['*'])->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['refresh'])->plainTextToken;

            return new LoginResponse($user, $accessToken, $refreshToken, 'Login successful');
        } else if ($request->input('phone') === '+6581234567' && $request->input('otp') !== '123456') {
            throw new ApiException(
                ApiErrorCode::UNAUTHORIZED,
                'Invalid OTP',
                ApiErrorCode::UNAUTHORIZED->value
            );
        }


        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        $verificationCheck = $twilio->verify->v2
            ->services(env('TWILIO_SERVICE_ID'))
            ->verificationChecks->create([
                'to' => $request->input('phone'), // The user's phone number
                'code' => $request->input('otp') // The OTP code input by the user
            ]);

        if ($verificationCheck->status === "approved") {
            // Generate tokens
            $accessToken = $user->createToken('auth_token', ['*'])->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['refresh'])->plainTextToken;

            return new LoginResponse($user, $accessToken, $refreshToken, 'Login successful');
        } else {
            // OTP verification failed
            throw new ApiException(
                ApiErrorCode::UNAUTHORIZED,
                'Invalid OTP',
                ApiErrorCode::UNAUTHORIZED->value
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/m/refresh-token",
     *     operationId="refreshTokens",
     *     summary="Refresh access and refresh tokens",
     *     tags={"Authentication"},
     *     security={{"Bearer Token": {}}, {"Access Token": {}}, {"Refresh Token": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tokens refreshed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Error: Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *               @OA\Property(property="error", type="object",
     *                   @OA\Property(property="code", type="integer", example=ApiErrorCode::UNAUTHORIZED),
     *                   @OA\Property(property="message", type="string", example="Invalid login credentials"),
     *                   @OA\Property(property="status", type="integer", example=401)
     *               )
     *         )
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Token Expired",
     *          @OA\JsonContent(
     *              type="object",
     *                @OA\Property(property="error", type="object",
     *                    @OA\Property(property="code", type="integer", example=ApiErrorCode::TOKEN_EXPIRED),
     *                    @OA\Property(property="message", type="string", example="Refresh Token Expired! Please re login"),
     *                    @OA\Property(property="status", type="integer", example=400)
     *                )
     *          )
     *      )
     * )
     */
    public function refresh(Request $request)
    {
        $accessToken = $request->header('X-Taste-Asia-Access-Token');
        $refreshToken = $request->header('X-Taste-Asia-Refresh-Token');


        if (!$accessToken || !$refreshToken) {
            throw new ApiException(ApiErrorCode::BAD_REQUEST, 'Access Token or refresh token is missing!', 401);
        }

        $refreshTokenModel = PersonalAccessToken::findToken($refreshToken);
        $accessTokenModel = PersonalAccessToken::findToken($accessToken);

        if (!$refreshTokenModel || !$accessTokenModel) {
            throw new ApiException(ApiErrorCode::INVALID_TOKEN, 'Invalid access or refresh token', 401);
        }

        // Check if the token types are correct
        if ($refreshTokenModel->name !== 'refresh_token' || $accessTokenModel->name !== 'auth_token') {
            throw new ApiException(ApiErrorCode::TOKEN_TYPE, 'Invalid Token Type', 401);
        }


        if ($refreshTokenModel->tokenable_id !== $accessTokenModel->tokenable_id) {
            throw new ApiException(ApiErrorCode::TOKEN_STOLEN, 'Refresh and Access token combination mismatched!', 401);
        }

        if ($refreshTokenModel->expires_at && $refreshTokenModel->expires_at->copy()->tz('UTC') < Carbon::now()) {
            throw new ApiException(ApiErrorCode::TOKEN_EXPIRED, 'Refresh Token Expired! Please re login', 400);
        }

        // If the token is valid, create a new access token
        $user = $refreshTokenModel->tokenable;

        // Revoke old tokens (optional)
        $user->tokens()->delete();

        // Generate new tokens
        $newAccessToken = $user->createToken(
            'auth_token',
            ['*'],
            now()->addMinutes(config('sanctum.expiration'))
        )->plainTextToken;
        $newRefreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDay())->plainTextToken;

        return new LoginResponse($user, $newAccessToken, $newRefreshToken, 'Token Refreshed');
    }

    #[OA\Post(
        path: '/m/lost-password',
        description: 'Generates a new password for the user and sends it to their phone by sms.',
        summary: 'Lost Password',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            description: 'User phone to reset password',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'phone', description: 'The phone number of the user', type: 'string'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successfully, new password sent to your phone by sms.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', description: 'Success status', type: 'boolean'),
                        new OA\Property(property: 'message', description: 'Response message', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            ),
        ]
    )]
    public function lostPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('phone', $request->input('phone'))->first();

        if (!$user) {
            throw new ApiException(
                ApiErrorCode::UNAUTHORIZED,
                'User with this phone not found',
                ApiErrorCode::UNAUTHORIZED->value
            );
        }

        // Generate a new 16-character alphanumeric password
        $newPassword = Str::random(16);

        // Update the user's password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $message = "Dear " . $user->name . ", Your new password for your Taste Asia account is: " . $newPassword . ". Please change your password upon logging in for security purposes.";
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_PHONE_NUMBER");
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $request->input('phone'),
            ['from' => $twilio_number, 'body' => $message]
        );

        return new BaseResponse(['success' => true], 'Password reset successfully, new password sent to your phone by sms.', 200);
    }
}
