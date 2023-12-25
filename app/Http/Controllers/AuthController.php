<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as RulesPassword;

class AuthController extends Controller
{
    public function register(Request $request) {

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|string|unique:users,email',
            'password' => ['required', RulesPassword::min(8)->mixedCase()->numbers()->symbols()],
            'password_confirmation' => 'min:8'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        $token = $user->createToken('MAIN')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request) {

        $credentials = $request->validate([
            'email' => 'required|email|string|exists:users,email',
            'password' => 'required',
            'remember' => 'boolean'
        ]);
        $remember = $credentials['remember'] ?? false;

        unset($credentials['remember']);

        if (!auth()->attempt($credentials, $remember)) {
            return response()->json([
                'error' => 'The provider credentials are not correct'
            ], 422);
        }

        $user = auth()->user();
        $token = $user->createToken('MAIN')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);

    }

    public function logout() {

        $user = auth()->user();

        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
