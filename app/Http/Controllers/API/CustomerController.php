<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\API\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;
use App\Enums\ApiErrorCode;
use App\Exceptions\ApiException;

class CustomerController extends Controller
{
    #[OA\Get(
        path: '/m/customer/details',
        description: 'Retrieves detailed information about the currently authenticated customer.',
        summary: 'Get Customer Details',
        security: [
            ['Bearer Token' => []],
            ['Access Token' => []]
        ],
        tags: ['Customer'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user_id', description: 'The ID of the user', type: 'integer'),
                        new OA\Property(property: 'name', description: 'The name of the user', type: 'string'),
                        new OA\Property(
                            property: 'email',
                            description: 'The email address of the user',
                            type: 'string'
                        ),
                        new OA\Property(property: 'customer_id', description: 'The customer ID', type: 'string'),
                        new OA\Property(property: 'customer_role', description: 'The customer role', type: 'string'),
                        new OA\Property(property: 'credit_balance', description: 'The credit balance', type: 'number'),
                        new OA\Property(property: 'cash_balance', description: 'The cash balance', type: 'number'),
                        new OA\Property(property: 'credit_split', description: 'The credit split', type: 'number'),
                        new OA\Property(property: 'cash_split', description: 'The cash split', type: 'number'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function getDetails(Request $request)
    {
        $user = $request->user();
        $customerData = [
            'user_id'        => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'customer_id'    => $user->customer->customer_id,
            'customer_role'  => $user->customer->role_name,
            'credit_balance' => $user->customer->credit_balance,
            'cash_balance'   => $user->customer->cash_balance,
            'credit_split'   => $user->customer->credit_split,
            'cash_split'     => $user->customer->cash_split,
        ];

        return new BaseResponse($customerData, 'Customer details retrieved successfully.', 200);
    }

    #[OA\Delete(
        path: '/m/customer/delete',
        description: 'Allows the currently authenticated customer to delete their account.',
        summary: 'Delete Customer Account',
        security: [
            ['Bearer Token' => []],
            ['Access Token' => []]
        ],
        tags: ['Customer'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Account deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', description: 'Success status', type: 'boolean'),
                        new OA\Property(property: 'message', description: 'Response message', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request'
            ),
        ]
    )]
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        DB::beginTransaction();
        try {
            // Delete customer and associated user data
            $user->customer()->delete();
            $user->delete();

            DB::commit();

            return new BaseResponse(['success' => true], 'Account deleted successfully.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return new BaseResponse(['success' => false], 'Failed to delete account.', 400);
        }
    }

    #[OA\Post(
        path: '/m/customer/reset-password',
        description: 'Allows the currently authenticated customer to reset their password.',
        summary: 'Reset Customer Password',
        security: [
            ['Bearer Token' => []],
            ['Access Token' => []]
        ],
        tags: ['Customer'],
        requestBody: new OA\RequestBody(
            description: 'Password reset data',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'old_password', description: 'Old password', type: 'string', format: 'password'),
                    new OA\Property(property: 'new_password', description: 'New password', type: 'string', format: 'password'),
                    new OA\Property(property: 'new_password_confirmation', description: 'New password confirmation', type: 'string', format: 'password'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', description: 'Success status', type: 'boolean'),
                        new OA\Property(property: 'message', description: 'Response message', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function resetPassword(Request $request)
    {
        $user = $request->user();

        // Validate the request
        $validatedData = $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string',
            'new_password_confirmation' => 'required|string',
        ]);

        if (
            strlen($request->input('new_password')) < 8 ||
            strlen($request->input('new_password_confirmation')) < 8
        ) {
            throw new APIException(
                ApiErrorCode::SERVER_ERROR,
                'New password and confirmation must be at least 8 characters long',
                ApiErrorCode::SERVER_ERROR->value
            );
        }

        if ($request->input('new_password') !== $request->input('new_password_confirmation')) {
            throw new APIException(ApiErrorCode::SERVER_ERROR, 'New password confirmation does not match', ApiErrorCode::SERVER_ERROR->value);
        }
        // Check if the old password is correct
        if (!Hash::check($validatedData['old_password'], $user->password)) {
            throw new ApiException(
                ApiErrorCode::SERVER_ERROR,
                'Old password is incorrect.',
                ApiErrorCode::SERVER_ERROR->value
            );
        }

        // Update the password
        $user->update([
            'password' => Hash::make($validatedData['new_password']),
            'is_first_login' => false
        ]);

        return new BaseResponse(['success' => true], 'Password reset successfully.', 200);
    }
}
