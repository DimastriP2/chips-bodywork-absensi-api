<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($data) {
            $user = User::where('email', $data['email'])->lockForUpdate()->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json(['message' => 'Login gagal'], 401);
            }

            // Preserve the existing single mobile session policy.
            $user->tokens()->delete();
            $token = $user->createToken('mobile-token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'token' => $token,
                'user' => $user,
            ]);
        }, 3);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();

            if (!Hash::check($data['old_password'], $user->password)) {
                return response()->json(['message' => 'Password lama salah.'], 422);
            }

            if (Hash::check($data['new_password'], $user->password)) {
                return response()->json([
                    'message' => 'Password baru tidak boleh sama dengan password lama.',
                ], 422);
            }

            $user->password = Hash::make($data['new_password']);
            $user->save();

            // Keep this device signed in; revoke other mobile sessions.
            $current = $request->user()->currentAccessToken();
            $tokens = $user->tokens();
            if ($current instanceof PersonalAccessToken) {
                $tokens->where('id', '!=', $current->getKey());
            }
            $tokens->delete();

            return response()->json(['message' => 'Password berhasil diubah.']);
        }, 3);
    }
}
