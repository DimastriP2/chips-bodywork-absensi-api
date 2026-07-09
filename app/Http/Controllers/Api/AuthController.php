<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login User
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {

            return response()->json([
                'message' => 'Login gagal'
            ], 401);
        }

        $user = Auth::user();

        // Hapus token lama (opsional agar tidak menumpuk)
        $user->tokens()->delete();

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Ganti Password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Cek password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah.'
            ], 422);
        }

        // Jangan gunakan password yang sama
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'Password baru tidak boleh sama dengan password lama.'
            ], 422);
        }

        // Simpan password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diubah.'
        ], 200);
    }
}