<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin || !$admin->checkPassword($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        $request->session()->regenerate();
        $request->session()->put('auth.token', $token);

        return response()->json([
            'success' => true,
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'name' => $admin->name,
                'email' => $admin->email
            ]
        ]);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get current admin info
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'admin' => [
                'id' => $request->user()->id,
                'username' => $request->user()->username,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ]
        ]);
    }
}
