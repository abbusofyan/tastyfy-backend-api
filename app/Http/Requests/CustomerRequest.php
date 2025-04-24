<?php

namespace App\Http\Requests;

use App\Enums\CustomerRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        if ($user->customer) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerUserId = $this->route('customer') ? $this->route('customer')->user_id : $this->input('user_id');
        $customerId = $this->route('customer') ? $this->route('customer')->id : $this->input('id');

        $rules = [
            'role'        => ['required', Rule::in(CustomerRole::cases())],
            'customer_id' => 'required|unique:customers,customer_id',
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:255|unique:users,phone',
            'email'       => 'nullable|string|email|max:255|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['role'] = ['nullable', Rule::in(CustomerRole::cases())];
            $rules['customer_id'] = [
                'required',
                Rule::unique('customers')->ignore($customerId),
            ];
            $rules['email'] = [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($customerUserId),
            ];
            $rules['phone'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($customerUserId),
            ];
            $rules['password'] = [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ];
        }

        // dd($rules);

        return $rules;
    }
}
