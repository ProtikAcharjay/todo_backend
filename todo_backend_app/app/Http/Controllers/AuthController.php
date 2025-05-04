<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'status' => 'success',
                'token' => $user->createToken('api-token')->plainTextToken
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed, please try again later.'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'token' => $user->createToken('api-token')->plainTextToken
            ], 200);

        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->only('email'),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Login failed, please try again later.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed, please try again later.'
            ], 500);
        }
    }
}
