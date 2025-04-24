<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\ApiErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\RegisterRequest;
use App\Http\Responses\API\Auth\LoginResponse;
use App\Http\Responses\API\ErrorResponse;
use App\Models\Customer;
use App\Models\CustomerRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Http\Responses\API\BaseResponse;
use App\Exceptions\ApiException;

class RegisterController extends Controller
{

    /**
     * @OA\Post(
     *     path="/m/register-send-otp",
     *     operationId="registerSendOTP",
     *     summary="Send OTP to a phone number for registration",
     *     tags={"Registration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="+15017122661")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="OTP sent successfully!")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send OTP",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function registerSendOTP(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {

            $existingUser = User::where('phone', $request->input('phone'))->first();

            if ($existingUser) {
                throw new ApiException(
                    ApiErrorCode::REGISTRATION_ERROR,
                    'Phone number already registered. Please log in or use a different phone number.',
                    ApiErrorCode::REGISTRATION_ERROR->value
                );
            }

            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

            $twilio->verify->v2
                ->services(env('TWILIO_SERVICE_ID'))
                ->verifications->create($request->input('phone'), 'sms');

            return new BaseResponse(
                ['success' => true],
                'OTP sent successfully. Please verify to continue registration. OTP will expire in 10 minutes.',
                200
            );
        } catch (\Exception $e) {
            return new ErrorResponse(
                ApiErrorCode::REGISTRATION_ERROR,
                $e->getMessage(),
                ApiErrorCode::REGISTRATION_ERROR->value
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/m/register",
     *     operationId="registerUser",
     *     summary="Register a new user",
     *     tags={"Registration"},
     *     security={{"Bearer Token": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration details (multipart/form-data)",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/RegisterRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="User successfully registered!"),
     *                 @OA\Property(property="data", type="object", ref="#/components/schemas/LoginResponse")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Registration failed due to an error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     * @throws \Throwable
     */
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        try {
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

            $verificationCheck = $twilio->verify->v2
                ->services(env('TWILIO_SERVICE_ID'))
                ->verificationChecks->create([
                    'to' => $request->input('phone'), // The user's phone number
                    'code' => $request->input('otp') // The OTP code input by the user
                ]);

            if ($verificationCheck->status === "approved") {
                // Generate tokens
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'phone'    => $request->phone,
                    'password' => Hash::make($request->password),
                ]);

                //create the customer for the user
                $customerData = [
                    'user_id'        => $user->id,
                    'customer_id'    => Customer::generateCustomerId(),
                    'credit_balance' => null,
                    'cash_balance'   => null,
                    'credit_split'   => 50,
                    'cash_split'     => 50,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];

                $customer = $user->customer()->create($customerData);
                $customerRole = CustomerRole::whereName(\App\Enums\CustomerRole::PUBLIC)->first();

                $customer->assignRole($customerRole);

                //            if ($request->hasFile('photo')) {
                //                $user->updateProfilePhoto($request->file('photo'));
                //            }

                DB::commit();

                $accessToken = $user->createToken('auth_token', ['*'])->plainTextToken;
                $refreshToken = $user->createToken('refresh_token', ['*'])->plainTextToken;

                return new LoginResponse($user, $accessToken, $refreshToken, 'User successfully registered!');
            } else {
                // OTP verification failed
                throw new ApiException(
                    ApiErrorCode::UNAUTHORIZED,
                    'Invalid OTP',
                    ApiErrorCode::UNAUTHORIZED->value
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return new ErrorResponse(
                ApiErrorCode::REGISTRATION_ERROR,
                $e->getMessage(),
                ApiErrorCode::REGISTRATION_ERROR->value
            );
        }
    }
}
