<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserController extends Controller
{
    // Displays the user registration form
    public function create() {
        return view('users.register');
    }

    // Validates and registers a new user, then logs them in and redirects to the home page
    public function store(Request $request) {
        $formFields = $request->validate([
            'name' => ['required', 'min:4', Rule::unique('users', 'name')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => 'required|confirmed|min:8'
        ]);

        $formFields['password'] = bcrypt($formFields['password']);

        $user = User::create($formFields);
        $user->last_login_at = now();
        $user->previous_login_at = now();
        $user->save();

        auth()->login($user);

        return redirect('/');
    }

    // Logs out the user, invalidates the session, and redirects to the home page
    public function logout(Request $request) {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // Displays the user login form
    public function login() {
        return view('users.login');
    }

    // Validates login credentials, attempts to authenticate the user, and redirects based on success or failure
    public function authenticate(Request $request) {
        $formFields = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required'
        ]);

         // Attempt to authenticate the user
        if (auth()->attempt($formFields)) {
            $request->session()->regenerate();

            $user = auth()->user();
            $today = now()->startOfDay();

            if ($user->last_login_at->startOfDay()->ne($today)) {
                $user->previous_login_at = $user->last_login_at;
                $user->last_login_at = now();
                $user->save();
            }

            return redirect('/');
        }

        return back()->withErrors(['email' => 'Invalid Credentials'])->onlyInput('email');
    }
}
