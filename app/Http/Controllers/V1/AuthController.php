<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            $token = $user->createToken('secure-login-token')->plainTextToken;

            return response()->json([
                'user' => new UserResource($user),
                'message' => 'User registered successfully',
                'status' => 201,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'input' => $request->all()
            ]);

            return response()->json([
                'message' => 'Registration failed. Please try again later.',
                'status' => 500
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $key = 'login-attempts:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again later.'
                ], 429);
            }

            RateLimiter::hit($key, 60); // 5 attempts per 60 seconds

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Failed login attempt', [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'message' => 'Invalid credential'
                ], 401);
            }

            // Check if already logged in
            $existingToken = $user->tokens()->where('name', 'login-' . $request->ip())->first();
            if ($existingToken) {
                return response()->json([
                    'message' => 'You are already logged in.',
                    'status' => 200
                ]);
            }

            RateLimiter::clear($key);

            $token = $user->createToken('login-' . $request->ip())->plainTextToken;

            return response()->json([
                'token' => $token,
                'expires_in' => now()->addHours(2)->timestamp,
                'message' => 'User logged in successfully',
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Login failed. Please try again later.',
                'status' => 500,
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'User logged out successfully',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'user_id' => optional(Auth::user())->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Logout failed. Please try again later.',
                'status' => 500
            ], 500);
        }
    }
}
