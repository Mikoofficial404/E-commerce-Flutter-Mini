<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
}
