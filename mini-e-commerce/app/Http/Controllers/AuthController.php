<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string',
            'email' => 'required|email|string|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
                'success' => false,
            ], 422);
        }

        $user = User::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => $request->get('password'),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Register User',
            'success' => true,
            'data' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors(),
                'success' => false,
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (! $token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Maaf email atau Password Salah',
                'success' => false,
            ], 401);
        }

        return response()->json([
            'message' => 'Login Success',
            'success' => true,
            'token' => $token,
            'user' => auth()->guard('api')->user(),
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'messages' => 'Logout Success',
            ], 200);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Logout Failed',
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar',
            ], 404);
        }

        PasswordResetOtp::where('email', $request->email)->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::create([
            'email' => $request->email,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($request->email)->send(new ResetPasswordOtpMail($otp, $user->username));

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda',
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $resetOtp = PasswordResetOtp::where('email', $request->email)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$resetOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak ditemukan. Silakan request ulang.',
            ], 404);
        }

        if (!$resetOtp->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP sudah expired. Silakan request ulang.',
            ], 400);
        }

        if (!Hash::check($request->otp, $resetOtp->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP valid. Silakan reset password Anda.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $resetOtp = PasswordResetOtp::where('email', $request->email)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$resetOtp || !$resetOtp->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau sudah expired',
            ], 400);
        }

        if (!Hash::check($request->otp, $resetOtp->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $resetOtp->update(['is_used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset',
        ], 200);
    }
}
