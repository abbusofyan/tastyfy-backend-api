<?php

namespace App\Http\Requests\API;

use App\Enums\ApiErrorCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     description="Request body for user registration",
 *     required={"name", "phone", "otp", "password"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="User's full name",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="User's phone number (e.g., +6588837454)",
 *         maxLength=20
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="User's unique email address",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="otp",
 *         type="integer",
 *         description="One-time password verification code",
 *         example="123456"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="User's password (will be hashed)",
 *         minLength=8
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         description="Password confirmation to ensure accuracy"
 *     )
 * )
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|string|email|max:255|unique:users',
            'phone'    => 'required|string|max:20|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'otp'      => 'required|numeric'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json([
                'error' => [
                    'code'    => ApiErrorCode::VALIDATION_ERROR->value,
                    'message' => 'The given data was invalid.',
                    'errors'  => $errors,
                ]
            ], 422)
        );
    }
}
