<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;


class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
    /**
     * Attempt to authenticate a new session.
     *
     * @param LoginRequest $request
     * @return mixed
     */
    public function store(LoginRequest $request)
    {

        // Find the user by email
        $user = User::where('email', $request->email)->first();
        if (!$user || !$user->hasRole('admin')) {
            throw ValidationException::withMessages([
                'email' => ['Only administrators are allowed to login.'],
            ]);
        }

        // Check if the email exists
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided email does not exist.'],
            ]);
        }

        // Check if the password matches
        if (!auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }


        // Authenticate the user
        $response = parent::store($request);
        // If authentication was successful
        if ($response->status() === 302) {
            // Get the authenticated user
            $user = User::where('email', $request->email)->firstOrFail();

            // Dispatch the LoggedIn event
            if ($user->temp_pass !== null) {
                return redirect()->route('verification.password.change', ['id' => $user->id]);
            }
        }

        // Return the authentication response
        return $response;
    }
}
