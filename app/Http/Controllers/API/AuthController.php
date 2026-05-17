<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:owner,tenant',
            'phone' => 'required'
        ]);

        $verificationStatus = $request->role === 'owner'
            ? 'pending'
            : 'approved';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'verification_status' => $verificationStatus
        ]);

        if ($user->role === 'tenant') {
            Tenant::create([
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'message' => 'Register success',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $user = User::where($loginField, $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Wrong password'
            ], 401);
        }

        if (
            $user->role === 'owner' &&
            $user->verification_status !== 'approved'
        ) {
            return response()->json([
                'message' => 'Owner account is not verified yet'
            ], 403);
        }

        if ($request->filled('fcm_token')) {
            $user->update([
                'fcm_token' => $request->fcm_token
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout success'
        ]);
    }
}
