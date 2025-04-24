<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
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

        $adminId = $this->route('user') ? $this->route('user')->id : $this->input('id');

        $rules = [
            'phone'       => 'required|string|unique:users,phone',
            'name'        => 'required|string|max:255',
            'status'      => 'required|numeric',
            'email'       => 'required|string|email|max:255|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['phone'] = [
                'required',
                Rule::unique('users')->ignore($adminId),
            ];
            $rules['email'] = [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($adminId),
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
