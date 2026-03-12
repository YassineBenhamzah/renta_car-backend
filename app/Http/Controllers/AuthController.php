<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 1. REGISTER
    public function register(Request $request)
    {
        // Validate input
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed', // expects password_confirmation field
            'role' => 'in:user,agent', // Admin usually created manually, but we can allow agent reg
            'cin' => 'nullable|string',
            'permis' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);
        // Create User
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role' => $fields['role'] ?? 'user',
            'cin' => $fields['cin'] ?? null,
            'permis' => $fields['permis'] ?? null,
            'phone' => $fields['phone'] ?? null,
            'address' => $fields['address'] ?? null,
        ]);
        // Create Token
        $token = $user->createToken('myapptoken')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }
    // 2. LOGIN
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);
        // Check email & password
        $user = User::where('email', $fields['email'])->first();
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json(['message' => 'Bad credentials'], 401);
        }
        // Create Token
        $token = $user->createToken('myapptoken')->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }
    // 3. LOGOUT
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
