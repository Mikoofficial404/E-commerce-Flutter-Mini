<?php

namespace App\Http\Controllers;

use App\Models\User;


class UserController extends Controller
{
    public function index()
    {
        $User = User::all();

        if (!$User) {
            return response()->json([
                'Success' => false,
                'Messages' => 'Error Get User',
                'data' => null,
            ]);
        }

        return response()->json([
            'messages' => 'Get All User',
            'success' => true,
            'data' => $User
        ]);
    }

    public function show($id)
    {
        $Users = User::find($id);

        if (!$Users) {
            return response()->json([
                'Success' => false,
                'Messages' => 'Error Get User',
                'data' => null,
            ]);
        }

        if ($Users !== 0) {
            return response()->json([
                'messages' => 'Cannot Get Id 0',
                'success' => false,
            ]);
        }

        return response()->json([
            'messages' => 'Get By Id',
            'success' => true,
            'data' => $Users,
        ]);
    }
}
